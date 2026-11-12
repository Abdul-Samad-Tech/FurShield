<?php

namespace App\Services;

use App\Models\User;

class DashboardNavigation
{
    /**
     * @param  array{cart: int, alerts: int, appointments: int}  $badges
     * @return list<array{label: string, url: string, route: string, icon: string, active: bool, tab: bool, badge: int, badge_key: ?string}>
     */
    public function items(User $user, array $badges): array
    {
        return collect(config('navigation.'.$user->role->value, []))
            ->map(fn (array $item): array => $this->present($item, $badges))
            ->all();
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array{cart: int, alerts: int, appointments: int}  $badges
     * @return array{label: string, url: string, route: string, icon: string, active: bool, tab: bool, badge: int, badge_key: ?string}
     */
    private function present(array $item, array $badges): array
    {
        $badgeKey = $item['badge'] ?? null;

        return [
            'label' => $item['label'],
            'url' => route($item['route']),
            'route' => $item['route'],
            'icon' => $item['icon'],
            'active' => request()->routeIs(...$item['active']),
            'tab' => (bool) ($item['tab'] ?? false),
            'badge' => $badgeKey ? (int) ($badges[$badgeKey] ?? 0) : 0,
            'badge_key' => $badgeKey,
        ];
    }
}
