<?php

namespace App\Services;

use InvalidArgumentException;

class PayrollMoney
{
    public static function cents(string $amount): int
    {
        if (! preg_match('/^\d{1,13}(?:\.\d{1,2})?$/', $amount)) {
            throw new InvalidArgumentException('Invalid payroll amount.');
        }

        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }

    public static function decimal(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function prorate(int $cents, int $paidDays, int $scheduledDays): int
    {
        if ($scheduledDays === 0) {
            return 0;
        }

        return intdiv(($cents * $paidDays) + intdiv($scheduledDays, 2), $scheduledDays);
    }
}
