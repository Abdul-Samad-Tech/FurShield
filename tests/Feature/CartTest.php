<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\ProductCategory;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pet;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_checks_the_line_already_in_the_cart_against_stock(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Owner]);
        $product = $this->product('Salmon kibble', ProductCategory::Food, 5, 'Grain-free adult dog food');

        $this->actingAs($owner)
            ->from(route('products.index'))
            ->post(route('owner.cart.add', $product), ['quantity' => 3])
            ->assertRedirect(route('products.index'));

        $this->assertSame(3, OrderItem::query()->first()->quantity);

        $this->actingAs($owner)
            ->from(route('products.index'))
            ->post(route('owner.cart.add', $product), ['quantity' => 3])
            ->assertRedirect(route('products.index'))
            ->assertSessionHasErrors([
                'quantity' => 'Only 5 left of Salmon kibble. Your cart already has 3.',
            ]);

        $this->assertSame(3, OrderItem::query()->first()->quantity);

        $this->actingAs($owner)
            ->from(route('products.index'))
            ->post(route('owner.cart.add', $product), ['quantity' => 2])
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('success');

        $this->assertSame(5, OrderItem::query()->first()->quantity);
        $this->assertSame(5, $product->fresh()->stock_quantity);
    }

    public function test_update_rejects_a_quantity_above_stock(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Owner]);
        $product = $this->product('Joint chews', ProductCategory::Health, 5, 'Chews for older dogs');

        $this->actingAs($owner)->post(route('owner.cart.add', $product), ['quantity' => 3]);
        $item = OrderItem::query()->first();

        $this->actingAs($owner)
            ->from(route('owner.cart.index'))
            ->put(route('owner.cart.update', $item), ['quantity' => 6])
            ->assertRedirect(route('owner.cart.index'))
            ->assertSessionHasErrors([
                'quantity' => 'Only 5 left of Joint chews.',
            ]);

        $this->assertSame(3, $item->fresh()->quantity);

        $this->actingAs($owner)
            ->from(route('owner.cart.index'))
            ->put(route('owner.cart.update', $item), ['quantity' => 4])
            ->assertRedirect(route('owner.cart.index'))
            ->assertSessionHas('success');

        $this->assertSame(4, $item->fresh()->quantity);
    }

    public function test_checkout_rolls_back_when_any_line_exceeds_stock_and_returns_a_validation_message(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Owner]);
        $bowl = $this->product('Travel bowl', ProductCategory::Accessories, 10, 'A folding bowl');
        $leash = $this->product('City leash', ProductCategory::Accessories, 10, 'A short leash');

        $this->actingAs($owner)->post(route('owner.cart.add', $bowl), ['quantity' => 2]);
        $this->actingAs($owner)->post(route('owner.cart.add', $leash), ['quantity' => 3]);
        $leash->update(['stock_quantity' => 1]);

        $this->actingAs($owner)
            ->from(route('owner.checkout.create'))
            ->post(route('owner.checkout.store'), $this->checkoutPayload($owner))
            ->assertRedirect(route('owner.checkout.create'))
            ->assertSessionHasErrors('cart');

        $this->assertStringContainsString(
            'Your cart asks for 3.',
            implode(' ', session('errors')->get('cart'))
        );

        $this->actingAs($owner)
            ->postJson(route('owner.checkout.store'), $this->checkoutPayload($owner))
            ->assertStatus(422)
            ->assertJsonPath('errors.cart.0', 'Only 1 left of City leash. Your cart asks for 3.');

        $this->assertSame(10, $bowl->fresh()->stock_quantity);
        $this->assertSame(1, $leash->fresh()->stock_quantity);
        $this->assertSame(OrderStatus::Cart, Order::query()->first()->status);
    }

    public function test_checkout_places_the_order_and_decrements_stock(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Owner]);
        $product = $this->product('Training clicker', ProductCategory::Training, 10, 'A clicker for dogs');

        $this->actingAs($owner)->post(route('owner.cart.add', $product), ['quantity' => 2]);

        $this->actingAs($owner)
            ->post(route('owner.checkout.store'), $this->checkoutPayload($owner))
            ->assertRedirect(route('owner.orders.show', Order::query()->first()))
            ->assertSessionHas('success');

        $this->assertSame(8, $product->fresh()->stock_quantity);
        $this->assertSame(OrderStatus::Placed, Order::query()->first()->status);
    }

    public function test_json_add_stays_on_the_shop_and_returns_the_cart(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Owner]);
        $product = $this->product('Feather wand', ProductCategory::Toys, 20, 'Interactive toy for indoor cats.');

        $this->actingAs($owner)
            ->from(route('products.index'))
            ->postJson(route('owner.cart.add', $product), ['quantity' => 1])
            ->assertOk()
            ->assertJsonPath('message', 'Feather wand added to your cart.')
            ->assertJsonPath('cart.count', 1)
            ->assertJsonPath('cart.items.0.quantity', 1);

        $this->actingAs($owner)
            ->from(route('products.index'))
            ->post(route('owner.cart.add', $product), ['quantity' => 1])
            ->assertRedirect(route('products.index'));
    }

    public function test_shop_recommends_for_the_pet_and_offers_buy_again(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Owner]);
        Pet::query()->create([
            'owner_id' => $owner->id,
            'name' => 'Luna',
            'species' => 'Cat',
            'age' => 2,
        ]);

        $wand = $this->product('Feather wand', ProductCategory::Toys, 20, 'Interactive toy for indoor cats.');
        $this->product('Salmon kibble', ProductCategory::Food, 40, 'Grain-free adult dog food');
        $this->product('Soft cat brush', ProductCategory::Grooming, 4, 'A brush for cats');

        $this->get(route('products.index'))->assertOk()->assertDontSee('Recommended for');

        $response = $this->actingAs($owner)->get(route('products.index'));
        $response->assertOk();
        $response->assertSee('Recommended for Luna');
        $response->assertSee('Low stock');
        $response->assertSee('Add to cart');
        $response->assertDontSee('Buy now');

        $html = $response->getContent();
        $slice = $this->between($html, 'id="recommended-for"', 'id="shop-grid"');
        $this->assertStringContainsString('Feather wand', $slice);
        $this->assertStringContainsString('Soft cat brush', $slice);
        $this->assertStringNotContainsString('Salmon kibble', $slice);

        $this->actingAs($owner)->post(route('owner.cart.add', $wand), ['quantity' => 1]);
        $this->actingAs($owner)->post(route('owner.checkout.store'), $this->checkoutPayload($owner));

        $again = $this->actingAs($owner)->get(route('products.index'));
        $again->assertSee('Buy again');
        $buyAgain = $this->between($again->getContent(), 'id="buy-again-heading"', 'id="shop-grid"');
        $this->assertStringContainsString('Feather wand', $buyAgain);
    }

    /**
     * @return array<string, string>
     */
    private function checkoutPayload(User $owner): array
    {
        return [
            'contact_name' => $owner->name,
            'contact_phone' => '03001234567',
            'contact_email' => $owner->email,
            'fulfillment' => 'pickup',
            'payment_method' => 'pay_on_collection',
        ];
    }

    private function product(string $name, ProductCategory $category, int $stock, string $description): Product
    {
        return Product::query()->create([
            'name' => $name,
            'category' => $category,
            'price' => 12.50,
            'description' => $description,
            'stock_quantity' => $stock,
            'is_active' => true,
        ]);
    }

    private function between(string $html, string $start, string $end): string
    {
        $from = strpos($html, $start);
        $to = strpos($html, $end);

        $this->assertNotFalse($from);
        $this->assertNotFalse($to);
        $this->assertGreaterThan($from, $to);

        return substr($html, $from, $to - $from);
    }
}
