<?php

namespace App\Enums;

enum AdoptionStatus: string
{
    case Available = 'available';
    case Pending = 'pending';
    case Adopted = 'adopted';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
