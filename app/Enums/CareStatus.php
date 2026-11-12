<?php

namespace App\Enums;

enum CareStatus: string
{
    case Overdue = 'overdue';
    case DueSoon = 'due_soon';
    case OnTrack = 'on_track';

    public function label(): string
    {
        return match ($this) {
            self::Overdue => 'Overdue',
            self::DueSoon => 'Due soon',
            self::OnTrack => 'On track',
        };
    }

    public function variant(): string
    {
        return match ($this) {
            self::Overdue => 'danger',
            self::DueSoon => 'warning',
            self::OnTrack => 'success',
        };
    }
}
