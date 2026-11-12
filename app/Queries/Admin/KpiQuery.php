<?php

namespace App\Queries\Admin;

use App\Enums\OrderStatus;
use App\Models\Appointment;
use App\Models\Order;
use App\Models\User;
use App\Support\Furshield;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class KpiQuery
{
    public function __construct(private ReportingWindow $window) {}

    /**
     * @return list<array{label: string, value: string, count: bool, icon: string, delta: string, trend: string, sparkline: list<int|float>, sparkline_label: string}>
     */
    public function cards(): array
    {
        $users = User::query()
            ->where('created_at', '>=', $this->window->previousStart())
            ->get(['created_at']);
        $appointments = Appointment::query()
            ->where('created_at', '>=', $this->window->previousStart())
            ->get(['created_at']);
        $orders = Order::query()
            ->where('status', OrderStatus::Placed)
            ->where(function ($query): void {
                $start = $this->window->previousStart();
                $query->where('order_date', '>=', $start)
                    ->orWhere(function ($inner) use ($start): void {
                        $inner->whereNull('order_date')->where('created_at', '>=', $start);
                    });
            })
            ->get(['total_amount', 'order_date', 'created_at']);

        $userMoments = $users->map(fn (User $user): CarbonInterface => $user->created_at);
        $visitMoments = $appointments->map(fn (Appointment $appointment): CarbonInterface => $appointment->created_at);
        $orderMoments = $orders->map(fn (Order $order): CarbonInterface => $order->order_date ?? $order->created_at);

        $userDelta = $this->countDelta(
            $userMoments->filter(fn (CarbonInterface $moment): bool => $this->window->inCurrent($moment))->count(),
            $userMoments->filter(fn (CarbonInterface $moment): bool => $this->window->inPrevious($moment))->count(),
        );
        $visitDelta = $this->countDelta(
            $visitMoments->filter(fn (CarbonInterface $moment): bool => $this->window->inCurrent($moment))->count(),
            $visitMoments->filter(fn (CarbonInterface $moment): bool => $this->window->inPrevious($moment))->count(),
        );
        $orderDelta = $this->countDelta(
            $orderMoments->filter(fn (CarbonInterface $moment): bool => $this->window->inCurrent($moment))->count(),
            $orderMoments->filter(fn (CarbonInterface $moment): bool => $this->window->inPrevious($moment))->count(),
        );

        $currentRevenue = $this->revenue($orders, true);
        $previousRevenue = $this->revenue($orders, false);
        $revenueDelta = $this->moneyDelta($currentRevenue, $previousRevenue);

        return [
            $this->card('New users', (string) $userDelta['current'], true, 'user', $userDelta, $this->countsByDay($userMoments), 'New users for each of the last 7 days'),
            $this->card('New appointments', (string) $visitDelta['current'], true, 'calendar', $visitDelta, $this->countsByDay($visitMoments), 'New appointments for each of the last 7 days'),
            $this->card('Placed orders', (string) $orderDelta['current'], true, 'shopping-bag', $orderDelta, $this->countsByDay($orderMoments), 'Placed orders for each of the last 7 days'),
            $this->card('Revenue', Furshield::money($currentRevenue), false, 'receipt', $revenueDelta, $this->revenueByDay($orders), 'Placed-order revenue for each of the last 7 days'),
        ];
    }

    /**
     * @param  array{current: int, trend: string, text: string}  $delta
     * @param  list<int|float>  $sparkline
     * @return array{label: string, value: string, count: bool, icon: string, delta: string, trend: string, sparkline: list<int|float>, sparkline_label: string}
     */
    private function card(string $label, string $value, bool $count, string $icon, array $delta, array $sparkline, string $sparklineLabel): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'count' => $count,
            'icon' => $icon,
            'delta' => $delta['text'],
            'trend' => $delta['trend'],
            'sparkline' => $sparkline,
            'sparkline_label' => $sparklineLabel,
        ];
    }

    /**
     * @return array{current: int, trend: string, text: string}
     */
    private function countDelta(int $current, int $previous): array
    {
        $diff = $current - $previous;

        if ($diff === 0) {
            return ['current' => $current, 'trend' => 'flat', 'text' => 'Same as the previous 7 days'];
        }

        return [
            'current' => $current,
            'trend' => $diff > 0 ? 'up' : 'down',
            'text' => ($diff > 0 ? '+' : '−').abs($diff).' vs the previous 7 days',
        ];
    }

    /**
     * @return array{current: int, trend: string, text: string}
     */
    private function moneyDelta(float $current, float $previous): array
    {
        $diff = round($current - $previous, 2);

        if ($diff === 0.0) {
            return ['current' => 0, 'trend' => 'flat', 'text' => 'Same as the previous 7 days'];
        }

        return [
            'current' => 0,
            'trend' => $diff > 0 ? 'up' : 'down',
            'text' => ($diff > 0 ? '+' : '−').Furshield::money(abs($diff)).' vs the previous 7 days',
        ];
    }

    /**
     * @param  Collection<int, CarbonInterface>  $moments
     * @return list<int>
     */
    private function countsByDay(Collection $moments): array
    {
        $days = $this->window->days();
        $counts = array_fill(0, count($days), 0);

        foreach ($moments as $moment) {
            $index = $this->window->dayIndex($moment, $days);

            if ($index !== null) {
                $counts[$index]++;
            }
        }

        return $counts;
    }

    /**
     * @param  Collection<int, Order>  $orders
     * @return list<float>
     */
    private function revenueByDay(Collection $orders): array
    {
        $days = $this->window->days();
        $totals = array_fill(0, count($days), 0.0);

        foreach ($orders as $order) {
            $moment = $order->order_date ?? $order->created_at;
            $index = $this->window->dayIndex($moment, $days);

            if ($index !== null && $this->window->inCurrent($moment)) {
                $totals[$index] += (float) $order->total_amount;
            }
        }

        return array_map(fn (float $total): float => round($total, 2), $totals);
    }

    /**
     * @param  Collection<int, Order>  $orders
     */
    private function revenue(Collection $orders, bool $current): float
    {
        return round((float) $orders->sum(function (Order $order) use ($current): float {
            $moment = $order->order_date ?? $order->created_at;
            $matches = $current ? $this->window->inCurrent($moment) : $this->window->inPrevious($moment);

            return $matches ? (float) $order->total_amount : 0.0;
        }), 2);
    }
}
