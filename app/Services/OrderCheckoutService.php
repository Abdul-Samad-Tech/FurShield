<?php

namespace App\Services;

use App\Enums\FulfillmentMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderCheckoutService
{
    public function __construct(private CardPayment $cards) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function place(User $owner, array $data): Order
    {
        return DB::transaction(function () use ($owner, $data): Order {
            $order = Order::query()
                ->where('owner_id', $owner->id)
                ->where('status', OrderStatus::Cart)
                ->lockForUpdate()
                ->first();

            $items = $order?->items()->lockForUpdate()->get() ?? collect();

            if ($order === null || $items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Your cart is empty.',
                ]);
            }

            $products = Product::query()
                ->whereIn('id', $items->pluck('product_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $errors = [];

            foreach ($items as $item) {
                $product = $products->get($item->product_id);

                if ($product === null || ! $product->is_active) {
                    $errors[] = 'A product in your cart is no longer available.';

                    continue;
                }

                if ($product->stock_quantity < $item->quantity) {
                    $errors[] = 'Only '.$product->stock_quantity.' left of '.$product->name.'. Your cart asks for '.$item->quantity.'.';
                }
            }

            if ($errors !== []) {
                throw ValidationException::withMessages([
                    'cart' => $errors,
                ]);
            }

            $method = PaymentMethod::from((string) $data['payment_method']);
            $card = null;

            if ($method === PaymentMethod::Card) {
                $card = $this->cards->authorize(
                    (string) $data['card_number'],
                    (string) $data['card_expiry'],
                    (string) $data['card_cvc'],
                );
            }

            foreach ($items as $item) {
                $products->get($item->product_id)->decrement('stock_quantity', $item->quantity);
            }

            $fulfillment = FulfillmentMethod::from((string) $data['fulfillment']);
            $paid = $method === PaymentMethod::Card;

            $order->update([
                'status' => OrderStatus::Placed,
                'order_date' => Carbon::now('UTC'),
                'contact_name' => $data['contact_name'],
                'contact_phone' => $data['contact_phone'],
                'contact_email' => $data['contact_email'],
                'fulfillment' => $fulfillment,
                'address_line' => $fulfillment === FulfillmentMethod::Delivery ? $data['address_line'] : null,
                'city' => $fulfillment === FulfillmentMethod::Delivery ? $data['city'] : null,
                'notes' => filled($data['notes'] ?? null) ? $data['notes'] : null,
                'payment_method' => $method,
                'payment_status' => $paid ? PaymentStatus::Paid : PaymentStatus::Pending,
            ]);

            $payment = Payment::query()->create([
                'order_id' => $order->id,
                'method' => $method,
                'status' => $paid ? PaymentStatus::Paid : PaymentStatus::Pending,
                'amount' => $order->total_amount,
                'reference' => $this->reference(),
                'card_brand' => $card['brand'] ?? null,
                'card_last4' => $card['last4'] ?? null,
                'paid_at' => $paid ? Carbon::now('UTC') : null,
            ]);

            $summary = $paid
                ? 'Paid with '.($card['brand'] ?? 'card').' ending '.($card['last4'] ?? '')
                : 'Pay when the order is collected.';

            app(NotificationService::class)->notify(
                $owner,
                'Order placed',
                'Order #'.$order->id.' is placed. '.$summary.' Reference '.$payment->reference.'.',
                'order',
                route('owner.orders.show', $order)
            );

            return $order->load('payment', 'items.product');
        });
    }

    public function cancel(User $owner, Order $order): Order
    {
        return DB::transaction(function () use ($owner, $order): Order {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            abort_unless($locked->owner_id === $owner->id, 404);

            if ($locked->status !== OrderStatus::Placed) {
                throw ValidationException::withMessages([
                    'order' => 'This order can no longer be cancelled.',
                ]);
            }

            $items = $locked->items()->lockForUpdate()->get();
            $products = Product::query()
                ->whereIn('id', $items->pluck('product_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                $product = $products->get($item->product_id);

                if ($product !== null) {
                    $product->increment('stock_quantity', $item->quantity);
                }
            }

            $payment = Payment::query()->where('order_id', $locked->id)->lockForUpdate()->first();
            $wasPaid = $payment?->status === PaymentStatus::Paid;
            $nextStatus = $wasPaid ? PaymentStatus::Refunded : PaymentStatus::Failed;

            $locked->update([
                'status' => OrderStatus::Cancelled,
                'payment_status' => $nextStatus,
            ]);

            $payment?->update([
                'status' => $nextStatus,
                'failure_reason' => $wasPaid ? null : 'Cancelled before collection.',
            ]);

            app(NotificationService::class)->notify(
                $owner,
                'Order cancelled',
                'Order #'.$locked->id.' was cancelled.'.($wasPaid ? ' The card payment is marked refunded.' : ''),
                'order',
                route('owner.orders.show', $locked)
            );

            return $locked->fresh(['payment', 'items.product']);
        });
    }

    private function reference(): string
    {
        do {
            $reference = 'FS-'.Str::upper(Str::random(8));
        } while (Payment::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
