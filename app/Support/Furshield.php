<?php

namespace App\Support;

final class Furshield
{
    public static function currencySymbol(): string
    {
        return (string) config('furshield.currency_symbol');
    }

    public static function money(float|int|string $amount): string
    {
        return self::currencySymbol().number_format((float) $amount, 2);
    }

    public static function supportPhone(): string
    {
        return (string) config('furshield.support_phone');
    }

    public static function emergencyPhone(): string
    {
        return (string) config('furshield.emergency_phone');
    }

    public static function emergencyTel(): ?string
    {
        $phone = trim(self::emergencyPhone());

        if ($phone === '') {
            return null;
        }

        $digits = preg_replace('/[^\d+]/', '', $phone) ?? '';

        if ($digits === '' || $digits === '+') {
            return null;
        }

        return 'tel:'.$digits;
    }
}
