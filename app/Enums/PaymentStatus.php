<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Paid = 'paid';
    case Pending = 'pending';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Paid => 'Paid',
            self::Pending => 'Pay on collection',
            self::Failed => 'Not collected',
            self::Refunded => 'Refunded',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Paid => 'success',
            self::Pending => 'warning',
            self::Failed => 'danger',
            self::Refunded => 'neutral',
        };
    }
}
