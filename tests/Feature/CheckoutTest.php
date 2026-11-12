<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Mail\OrderReceiptMail;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    public function test_checkout_page_shows_the_cart_and_an_empty_state(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Owner, 'name' => 'Ayesha Khan']);

        $this->actingAs($owner)
            ->get(route('owner.checkout.create'))
            ->assertOk()
            ->assertSee('Nothing to check out');

        $product = $this->product(8);
        $this->actingAs($owner)->post(route('owner.cart.add', $product), ['quantity' => 1]);

        $this->actingAs($owner)
            ->get(route('owner.checkout.create'))
            ->assertOk()
            ->assertSee('Payment')
            ->assertSee($product->name)
            ->assertSee('Ayesha Khan');
    }

    public function test_card_payment_places_the_order_without_storing_the_card_number(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Owner]);
        $product = $this->product(6);

        $this->actingAs($owner)->post(route('owner.cart.add', $product), ['quantity' => 2]);

        $this->actingAs($owner)
            ->post(route('owner.checkout.store'), $this->payload([
                'fulfillment' => 'delivery',
                'address_line' => '12 Maple Street',
                'city' => 'Karachi',
                'payment_method' => 'card',
                'cardholder' => 'Ayesha Khan',
                'card_number' => '4242 4242 4242 4242',
                'card_expiry' => '12/30',
                'card_cvc' => '123',
            ]))
            ->assertRedirect();

        $order = Order::query()->where('status', OrderStatus::Placed)->firstOrFail();
        $payment = Payment::query()->where('order_id', $order->id)->firstOrFail();

        $this->assertSame(PaymentStatus::Paid, $payment->status);
        $this->assertSame('4242', $payment->card_last4);
        $this->assertSame('Visa', $payment->card_brand);
        $this->assertSame('12 Maple Street', $order->address_line);
        $this->assertSame(4, $product->fresh()->stock_quantity);
        $this->assertDatabaseMissing('payments', ['card_last4' => '4242424242424242']);

        $this->actingAs($owner)->post(route('owner.orders.cancel', $order));
        $this->assertSame(PaymentStatus::Refunded, $order->fresh()->payment_status);
        $this->assertSame(6, $product->fresh()->stock_quantity);

        Mail::assertSent(OrderReceiptMail::class, function (OrderReceiptMail $mail) use ($order, $payment): bool {
            $html = $mail->render();

            return $mail->hasTo('owner@furshield.test')
                && $mail->order->is($order)
                && str_contains($html, $payment->reference)
                && str_contains($html, 'ending 4242')
                && ! str_contains($html, '4242 4242 4242 4242');
        });

        $this->actingAs($owner)
            ->get(route('owner.orders.show', $order))
            ->assertOk()
            ->assertSee($payment->reference)
            ->assertSee('ending 4242')
            ->assertSee('Download receipt')
            ->assertSee('same receipt sent to owner@furshield.test')
            ->assertDontSee('4242 4242 4242 4242');

        $this->actingAs($owner)
            ->get(route('owner.orders.receipt', $order))
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename="furshield-receipt-'.$payment->reference.'.html"')
            ->assertSee($payment->reference)
            ->assertSee('ending 4242')
            ->assertDontSee('4242 4242 4242 4242');
    }

    public function test_a_declined_card_leaves_the_cart_and_stock_unchanged(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Owner]);
        $product = $this->product(6);

        $this->actingAs($owner)->post(route('owner.cart.add', $product), ['quantity' => 1]);

        $this->actingAs($owner)
            ->from(route('owner.checkout.create'))
            ->post(route('owner.checkout.store'), $this->payload([
                'payment_method' => 'card',
                'cardholder' => 'Ayesha Khan',
                'card_number' => '4000000000000002',
                'card_expiry' => '12/30',
                'card_cvc' => '123',
            ]))
            ->assertRedirect(route('owner.checkout.create'))
            ->assertSessionHasErrors('card_number');

        $this->assertArrayNotHasKey('card_number', session()->getOldInput() ?? []);
        $this->assertArrayNotHasKey('card_cvc', session()->getOldInput() ?? []);

        $this->assertSame(OrderStatus::Cart, Order::query()->first()->status);
        $this->assertSame(6, $product->fresh()->stock_quantity);
        $this->assertSame(0, Payment::query()->count());
        Mail::assertNothingSent();
    }

    public function test_delivery_requires_an_address(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Owner]);
        $product = $this->product(4);
        $this->actingAs($owner)->post(route('owner.cart.add', $product), ['quantity' => 1]);

        $this->actingAs($owner)
            ->from(route('owner.checkout.create'))
            ->post(route('owner.checkout.store'), $this->payload([
                'fulfillment' => 'delivery',
            ]))
            ->assertRedirect(route('owner.checkout.create'))
            ->assertSessionHasErrors(['address_line', 'city']);

        $this->assertSame(OrderStatus::Cart, Order::query()->first()->status);
    }

    public function test_the_owner_can_cancel_and_stock_returns(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Owner]);
        $other = User::factory()->create(['role' => UserRole::Owner]);
        $product = $this->product(5);

        $this->actingAs($owner)->post(route('owner.cart.add', $product), ['quantity' => 2]);
        $this->actingAs($owner)->post(route('owner.checkout.store'), $this->payload());

        $order = Order::query()->where('status', OrderStatus::Placed)->firstOrFail();
        $this->assertSame(3, $product->fresh()->stock_quantity);

        $this->actingAs($other)
            ->get(route('owner.orders.show', $order))
            ->assertNotFound();

        $this->actingAs($other)
            ->get(route('owner.orders.receipt', $order))
            ->assertNotFound();

        $this->actingAs($other)
            ->post(route('owner.orders.cancel', $order))
            ->assertNotFound();

        $this->actingAs($owner)
            ->post(route('owner.orders.cancel', $order))
            ->assertRedirect(route('owner.orders.show', $order));

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(PaymentStatus::Failed, $order->fresh()->payment_status);
        $this->assertSame(5, $product->fresh()->stock_quantity);

        $this->actingAs($owner)
            ->post(route('owner.orders.cancel', $order))
            ->assertSessionHasErrors('order');
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'contact_name' => 'Ayesha Khan',
            'contact_phone' => '03001234567',
            'contact_email' => 'owner@furshield.test',
            'fulfillment' => 'pickup',
            'payment_method' => 'pay_on_collection',
        ], $overrides);
    }

    private function product(int $stock): Product
    {
        return Product::query()->create([
            'name' => 'Salmon kibble',
            'category' => 'food',
            'price' => 12.50,
            'description' => 'Grain-free adult dog food',
            'stock_quantity' => $stock,
            'is_active' => true,
        ]);
    }
}
