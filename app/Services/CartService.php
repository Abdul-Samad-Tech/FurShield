<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Support\DummyImages;
use App\Support\Furshield;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    /**
     * @return array{count: int, total_label: string, currency: string, checkout_url: string, cart_url: string, shop_url: string, items: list<array<string, mixed>>}
     */
    public function snapshot(User $owner): array
    {
        $order = $this->findCart($owner);

        $items = $order?->items ?? collect();

        return [
            'count' => $items->count(),
            'total_label' => Furshield::money($order?->total_amount ?? 0),
            'currency' => Furshield::currencySymbol(),
            'checkout_url' => route('owner.cart.checkout'),
            'cart_url' => route('owner.cart.index'),
            'shop_url' => route('products.index'),
            'items' => $items->map(fn (OrderItem $item): array => $this->presentItem($item))->values()->all(),
        ];
    }

    public function add(User $owner, Product $product, int $quantity): string
    {
        return DB::transaction(function () use ($owner, $product, $quantity): string {
            $locked = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            $this->guardAvailable($locked);

            $order = $this->findCart($owner, lock: true) ?? $this->createCart($owner);
            $item = $order->items()->where('product_id', $locked->id)->lockForUpdate()->first();
            $already = (int) ($item?->quantity ?? 0);
            $next = $already + $quantity;

            if ($next > $locked->stock_quantity) {
                throw ValidationException::withMessages([
                    'quantity' => $this->stockMessage($locked, $already),
                ]);
            }

            if ($next > 20) {
                throw ValidationException::withMessages([
                    'quantity' => 'You can add at most 20 of '.$locked->name.'.',
                ]);
            }

            if ($item !== null) {
                $item->update(['quantity' => $next]);
            } else {
                $order->items()->create([
                    'product_id' => $locked->id,
                    'quantity' => $quantity,
                    'price_each' => $locked->price,
                ]);
            }

            $order->unsetRelation('items');
            $order->load('items');
            $order->recalculateTotal();

            return $locked->name.' added to your cart.';
        });
    }

    public function update(User $owner, OrderItem $item, int $quantity): string
    {
        return DB::transaction(function () use ($owner, $item, $quantity): string {
            $this->owned($owner, $item);

            $lockedItem = OrderItem::query()->whereKey($item->id)->lockForUpdate()->firstOrFail();
            $product = Product::query()->whereKey($lockedItem->product_id)->lockForUpdate()->first();

            if ($product === null || ! $product->is_active) {
                throw ValidationException::withMessages([
                    'quantity' => 'That product is no longer available.',
                ]);
            }

            if ($quantity > $product->stock_quantity) {
                throw ValidationException::withMessages([
                    'quantity' => $this->stockMessage($product, null),
                ]);
            }

            if ($quantity > 20) {
                throw ValidationException::withMessages([
                    'quantity' => 'You can add at most 20 of '.$product->name.'.',
                ]);
            }

            $lockedItem->update(['quantity' => $quantity]);

            $order = Order::query()->with('items')->find($lockedItem->order_id);
            $order?->recalculateTotal();

            return 'Cart updated.';
        });
    }

    public function remove(User $owner, OrderItem $item): string
    {
        return DB::transaction(function () use ($owner, $item): string {
            $this->owned($owner, $item);
            $name = $item->product?->name ?? 'That item';
            $item->delete();

            $order = $this->findCart($owner);
            if ($order !== null) {
                $order->unsetRelation('items');
                $order->load('items');
                $order->recalculateTotal();
            }

            return $name.' removed from your cart.';
        });
    }

    public function checkout(User $owner): string
    {
        return DB::transaction(function () use ($owner): string {
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

            foreach ($items as $item) {
                $products->get($item->product_id)->decrement('stock_quantity', $item->quantity);
            }

            $order->update([
                'status' => OrderStatus::Placed,
                'order_date' => Carbon::now('UTC'),
            ]);

            app(NotificationService::class)->notify(
                $owner,
                'Order placed',
                'Order #'.$order->id.' was placed. Payment and delivery are not part of FurShield.',
                'order',
                route('owner.cart.orders')
            );

            return 'Order placed. Payment and delivery are not part of FurShield.';
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function presentItem(OrderItem $item): array
    {
        $product = $item->product;
        $image = DummyImages::describe($product?->image_path, 'product', (int) ($product?->id ?? $item->id));

        return [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'name' => $product?->name ?? 'Unavailable product',
            'quantity' => (int) $item->quantity,
            'stock' => (int) ($product?->stock_quantity ?? 0),
            'price_each' => (float) $item->price_each,
            'price_label' => Furshield::money($item->price_each),
            'line_label' => Furshield::money((float) $item->price_each * (int) $item->quantity),
            'image' => $image['url'],
            'url' => $product ? route('products.show', $product) : route('products.index'),
            'add_url' => $product ? route('owner.cart.add', $product) : null,
            'update_url' => route('owner.cart.update', $item),
            'remove_url' => route('owner.cart.remove', $item),
            'pending' => false,
        ];
    }

    private function guardAvailable(Product $product): void
    {
        if (! $product->is_active) {
            throw ValidationException::withMessages([
                'quantity' => $product->name.' is not available.',
            ]);
        }
    }

    private function stockMessage(Product $product, ?int $already): string
    {
        if ($product->stock_quantity < 1) {
            return $product->name.' is out of stock.';
        }

        if ($already !== null && $already > 0) {
            return 'Only '.$product->stock_quantity.' left of '.$product->name.'. Your cart already has '.$already.'.';
        }

        return 'Only '.$product->stock_quantity.' left of '.$product->name.'.';
    }

    private function owned(User $owner, OrderItem $item): void
    {
        $order = $this->findCart($owner);

        abort_unless($order !== null && $item->order_id === $order->id, 403);
    }

    private function findCart(User $owner, bool $lock = false): ?Order
    {
        $query = Order::query()
            ->where('owner_id', $owner->id)
            ->where('status', OrderStatus::Cart)
            ->with('items.product');

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private function createCart(User $owner): Order
    {
        $order = Order::query()->create([
            'owner_id' => $owner->id,
            'total_amount' => 0,
            'status' => OrderStatus::Cart,
        ]);
        $order->setRelation('items', collect());

        return $order;
    }
}
