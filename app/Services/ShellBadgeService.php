<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\OrderStatus;
use App\Models\AppNotification;
use App\Models\Appointment;
use App\Models\User;

class ShellBadgeService
{
    /**
     * @return array{cart: int, alerts: int, appointments: int}
     */
    public function forUser(?User $user): array
    {
        if ($user === null) {
            return ['cart' => 0, 'alerts' => 0, 'appointments' => 0];
        }

        return [
            'cart' => $this->cartCount($user),
            'alerts' => $this->unreadAlerts($user),
            'appointments' => $this->pendingAppointments($user),
        ];
    }

    private function cartCount(User $user): int
    {
        return (int) ($user->orders()
            ->where('status', OrderStatus::Cart)
            ->withCount('items')
            ->first()
            ?->items_count ?? 0);
    }

    private function unreadAlerts(User $user): int
    {
        return AppNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    private function pendingAppointments(User $user): int
    {
        $query = Appointment::query()->where('status', AppointmentStatus::Pending);

        if ($user->isOwner()) {
            $query->where('owner_id', $user->id);
        } elseif ($user->isVet()) {
            $query->where('vet_id', $user->id);
        } else {
            return 0;
        }

        return $query->count();
    }
}
