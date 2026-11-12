<?php

namespace App\Queries\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;

class OrderRevenueQuery
{
    public function __construct(private ReportingWindow $window) {}

    /**
     * @return array{blank: bool, labels: list<string>, orders: list<int>, revenue: list<float>}
     */
    public function series(): array
    {
        $weeks = $this->window->weeks();
        $orders = array_fill(0, count($weeks), 0);
        $revenue = array_fill(0, count($weeks), 0.0);

        $placed = Order::query()
            ->where('status', OrderStatus::Placed)
            ->where(function ($query) use ($weeks): void {
                $start = $weeks[0];
                $query->where('order_date', '>=', $start)
                    ->orWhere(function ($inner) use ($start): void {
                        $inner->whereNull('order_date')->where('created_at', '>=', $start);
                    });
            })
            ->get(['total_amount', 'order_date', 'created_at']);

        foreach ($placed as $order) {
            $moment = $order->order_date ?? $order->created_at;
            $index = $this->window->weekIndex($moment, $weeks);

            if ($index === null) {
                continue;
            }

            $orders[$index]++;
            $revenue[$index] += (float) $order->total_amount;
        }

        $revenue = array_map(fn (float $amount): float => round($amount, 2), $revenue);

        return [
            'blank' => array_sum($orders) === 0,
            'labels' => $this->window->weekLabels(),
            'orders' => $orders,
            'revenue' => $revenue,
        ];
    }
}
