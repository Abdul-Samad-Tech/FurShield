<?php

namespace App\Enums;

enum HealthRecordType: string
{
    case Vaccination = 'vaccination';
    case Deworming = 'deworming';
    case Checkup = 'checkup';
    case Surgery = 'surgery';
    case Illness = 'illness';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Vaccination => 'Vaccination',
            self::Deworming => 'Deworming',
            self::Checkup => 'Checkup',
            self::Surgery => 'Surgery',
            self::Illness => 'Illness',
            self::Other => 'Other',
        };
    }

    public function recurrenceDays(): ?int
    {
        return match ($this) {
            self::Vaccination, self::Checkup => 365,
            self::Deworming => 90,
            self::Surgery, self::Illness, self::Other => null,
        };
    }

    public function optionLabel(): string
    {
        $days = $this->recurrenceDays();

        return $days === null
            ? $this->label().' · does not repeat'
            : $this->label().' · every '.$days.' days';
    }
}
