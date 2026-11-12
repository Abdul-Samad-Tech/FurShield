<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

class CardPayment
{
    /**
     * Check the card and return only the brand and last four digits.
     * The full number and the security code are not returned.
     *
     * @return array{brand: string, last4: string}
     */
    public function authorize(string $number, string $expiry, string $cvc): array
    {
        $digits = preg_replace('/\D+/', '', $number) ?? '';

        if (strlen($digits) < 13 || strlen($digits) > 19 || ! $this->luhn($digits)) {
            throw ValidationException::withMessages([
                'card_number' => 'Enter a valid card number.',
            ]);
        }

        if ($digits === '4000000000000002') {
            throw ValidationException::withMessages([
                'card_number' => 'The card was declined. Try another card, or choose pay on collection.',
            ]);
        }

        if (in_array(preg_match('/^\d{3,4}$/', $cvc), [0, false], true)) {
            throw ValidationException::withMessages([
                'card_cvc' => 'Enter the 3 or 4 digit security code.',
            ]);
        }

        if (preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', trim($expiry), $matches) !== 1) {
            throw ValidationException::withMessages([
                'card_expiry' => 'Enter the expiry as MM/YY.',
            ]);
        }

        $year = 2000 + (int) $matches[2];
        $month = (int) $matches[1];
        $now = now('UTC');

        if ($year < (int) $now->year || ($year === (int) $now->year && $month < (int) $now->month)) {
            throw ValidationException::withMessages([
                'card_expiry' => 'This card is expired.',
            ]);
        }

        return [
            'brand' => $this->brand($digits),
            'last4' => substr($digits, -4),
        ];
    }

    private function luhn(string $digits): bool
    {
        $sum = 0;
        $alternate = false;

        for ($index = strlen($digits) - 1; $index >= 0; $index--) {
            $value = (int) $digits[$index];

            if ($alternate) {
                $value *= 2;

                if ($value > 9) {
                    $value -= 9;
                }
            }

            $sum += $value;
            $alternate = ! $alternate;
        }

        return $sum % 10 === 0;
    }

    private function brand(string $digits): string
    {
        return match ($digits[0]) {
            '4' => 'Visa',
            '5' => 'Mastercard',
            '3' => 'American Express',
            default => 'Card',
        };
    }
}
