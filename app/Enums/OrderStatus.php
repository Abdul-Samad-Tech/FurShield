<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Cart = 'cart';
    case Placed = 'placed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
