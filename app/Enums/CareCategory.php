<?php

namespace App\Enums;

enum CareCategory: string
{
    case Feeding = 'feeding';
    case Hygiene = 'hygiene';
    case Exercise = 'exercise';
    case Training = 'training';
    case Health = 'health';
    case Faq = 'faq';

    public function label(): string
    {
        return strtoupper($this->value) === 'FAQ' ? 'FAQ' : ucfirst($this->value);
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
