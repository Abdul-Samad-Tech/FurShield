<?php

namespace App\Queries\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\Furshield;
use Illuminate\Support\LazyCollection;

class OrderExportQuery
{
    /**
     * @return LazyCollection<int, array{0: int, 1: ?string, 2: ?string, 3: string, 4: string, 5: string, 6: string, 7: ?string, 8: string}>
     */
    public function rows(): LazyCollection
    {
        return Order::query()
            ->whereIn('status', [OrderStatus::Placed, OrderStatus::Cancelled])
            ->with(['owner', 'items.product', 'payment'])
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->lazy(200)
            ->map(fn (Order $order): array => [
                $order->id,
                $order->owner?->name,
                $order->owner?->email,
                $order->status->label(),
                $order->payment_status?->label() ?? '',
                $order->fulfillment?->label() ?? '',
                Furshield::money((float) $order->total_amount),
                ($order->order_date ?? $order->created_at)?->format('Y-m-d H:i'),
                $order->items->map(fn (OrderItem $item): string => $item->quantity.' × '.($item->product?->name ?? 'Removed product'))->implode('; '),
            ]);
    }
}
