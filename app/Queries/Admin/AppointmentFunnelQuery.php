<?php

namespace App\Queries\Admin;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;

class AppointmentFunnelQuery
{
    /**
     * @return array{blank: bool, stages: list<array{key: string, label: string, count: int, width: int}>}
     */
    public function series(): array
    {
        $counts = Appointment::query()
            ->toBase()
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stages = array_map(function (AppointmentStatus $status) use ($counts): array {
            return [
                'key' => $status->value,
                'label' => $status->label(),
                'count' => (int) ($counts[$status->value] ?? 0),
                'width' => 0,
            ];
        }, AppointmentStatus::cases());

        $max = max(array_column($stages, 'count'));
        $total = array_sum(array_column($stages, 'count'));

        foreach ($stages as $index => $stage) {
            if ($stage['count'] === 0 || $max === 0) {
                continue;
            }

            $stages[$index]['width'] = max(8, (int) round(($stage['count'] / $max) * 100));
        }

        return [
            'blank' => $total === 0,
            'stages' => $stages,
        ];
    }
}
