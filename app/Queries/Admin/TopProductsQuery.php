<?php

namespace App\Queries\Admin;

use App\Enums\OrderStatus;
use App\Models\OrderItem;
use App\Models\Product;

class TopProductsQuery
{
    /**
     * @return array{blank: bool, rows: list<array{name: string, units: int, revenue: float}>}
     */
    public function series(): array
    {
        $totals = OrderItem::query()
            ->toBase()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', OrderStatus::Placed->value)
            ->groupBy('order_items.product_id')
            ->select('order_items.product_id')
            ->selectRaw('SUM(order_items.quantity) as units')
            ->selectRaw('SUM(order_items.quantity * order_items.price_each) as revenue')
            ->orderByRaw('SUM(order_items.quantity) DESC')
            ->limit(5)
            ->get();

        $products = Product::query()
            ->whereIn('id', $totals->pluck('product_id'))
            ->get(['id', 'name'])
            ->keyBy('id');

        $rows = [];

        foreach ($totals as $total) {
            $product = $products->get($total->product_id);

            if ($product === null) {
                continue;
            }

            $rows[] = [
                'name' => $product->name,
                'units' => (int) $total->units,
                'revenue' => round((float) $total->revenue, 2),
            ];
        }

        return [
            'blank' => $rows === [],
            'rows' => $rows,
        ];
    }
}
