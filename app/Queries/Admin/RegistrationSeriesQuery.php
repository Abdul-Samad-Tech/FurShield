<?php

namespace App\Queries\Admin;

use App\Enums\UserRole;
use App\Models\User;

class RegistrationSeriesQuery
{
    public function __construct(private ReportingWindow $window) {}

    /**
     * @return array{blank: bool, labels: list<string>, series: list<array{key: string, label: string, values: list<int>}>}
     */
    public function series(): array
    {
        $weeks = $this->window->weeks();
        $counts = [];

        foreach (UserRole::cases() as $role) {
            $counts[$role->value] = array_fill(0, count($weeks), 0);
        }

        $users = User::query()
            ->where('created_at', '>=', $weeks[0])
            ->get(['role', 'created_at']);

        foreach ($users as $user) {
            $index = $this->window->weekIndex($user->created_at, $weeks);

            if ($index === null) {
                continue;
            }

            $counts[$user->role->value][$index]++;
        }

        $series = array_map(fn (UserRole $role): array => [
            'key' => $role->value,
            'label' => $role->label(),
            'values' => $counts[$role->value],
        ], UserRole::cases());

        $total = array_sum(array_map(fn (array $row): int => array_sum($row['values']), $series));

        return [
            'blank' => $total === 0,
            'labels' => $this->window->weekLabels(),
            'series' => $series,
        ];
    }
}
