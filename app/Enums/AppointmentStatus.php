<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rescheduled = 'rescheduled';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function variant(): string
    {
        return match ($this) {
            self::Approved => 'success',
            self::Cancelled => 'danger',
            self::Completed => 'neutral',
            self::Pending, self::Rescheduled => 'warning',
        };
    }
}
