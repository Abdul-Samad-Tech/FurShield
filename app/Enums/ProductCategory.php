<?php

namespace App\Enums;

enum ProductCategory: string
{
    case Food = 'food';
    case Grooming = 'grooming';
    case Toys = 'toys';
    case Health = 'health';
    case Accessories = 'accessories';
    case Training = 'training';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
