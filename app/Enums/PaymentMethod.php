<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Card = 'card';
    case PayOnCollection = 'pay_on_collection';

    public function label(): string
    {
        return match ($this) {
            self::Card => 'Card',
            self::PayOnCollection => 'Pay on collection',
        };
    }
}
