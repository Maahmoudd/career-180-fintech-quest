<?php

namespace App\Support;

use InvalidArgumentException;
use OverflowException;

final class IntegerMath
{
    public const MAX_AMOUNT = 1_000_000_000_000;

    public static function multiply(int $left, int $right): int
    {
        if ($left < 0 || $right < 0) {
            throw new InvalidArgumentException('Expected nonnegative operands.');
        }
        if ($right !== 0 && $left > intdiv(PHP_INT_MAX, $right)) {
            throw new OverflowException('Integer multiplication overflow.');
        }

        return $left * $right;
    }

    public static function proportion(int $amount, int $numerator, int $denominator): int
    {
        if ($denominator <= 0) {
            throw new InvalidArgumentException('Denominator must be positive.');
        }

        return intdiv(self::multiply($amount, $numerator), $denominator);
    }

    public static function days(string $start, string $end): int
    {
        return (int) (new \DateTimeImmutable($start, new \DateTimeZone('UTC')))->diff(new \DateTimeImmutable($end, new \DateTimeZone('UTC')))->format('%r%a');
    }

    public static function format(int $minor, string $currency = 'USD'): string
    {
        $digits = (string) abs($minor);
        $digits = str_pad($digits, 3, '0', STR_PAD_LEFT);

        return ($minor < 0 ? '-' : '').substr($digits, 0, -2).'.'.substr($digits, -2).' '.$currency;
    }
}
