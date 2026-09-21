<?php

use App\Domain\Services\RevenueAllocationService;
use App\Support\IntegerMath;
use App\Support\Money;

test('formats signed minor units without floating point', function () {
    expect(IntegerMath::format(-12345, 'EGP'))->toBe('-123.45 EGP');
    expect(IntegerMath::format(1))->toBe('0.01 USD');
});
test('calculates basis points exactly and rejects mixed currencies', function () {
    expect((new Money(10001))->percentage(2000)->minor)->toBe(2000);
    expect(fn () => (new Money(100))->add(new Money(100, 'EUR')))->toThrow(InvalidArgumentException::class);
});
test('rejects unsupported money and unsafe arithmetic', function () {
    expect(fn () => new Money(-1))->toThrow(InvalidArgumentException::class);
    expect(fn () => new Money(1, 'JPY'))->toThrow(InvalidArgumentException::class);
    expect(fn () => IntegerMath::multiply(PHP_INT_MAX, 2))->toThrow(OverflowException::class);
});
test('allocates exactly with deterministic residuals', function (int $amount, array $expected) {
    $rows = (new RevenueAllocationService)->allocate($amount, [['instructor_id' => 2, 'weight' => 1], ['instructor_id' => 1, 'weight' => 1], ['instructor_id' => 3, 'weight' => 1]]);
    expect(array_column($rows, 'amount', 'instructor_id'))->toBe($expected);
})->with([[0, [1 => 0, 2 => 0, 3 => 0]], [2, [1 => 1, 2 => 1, 3 => 0]], [9, [1 => 3, 2 => 3, 3 => 3]], [10, [1 => 4, 2 => 3, 3 => 3]]]);
test('conserves large allocations with intermediate products above integer range', function () {
    $rows = (new RevenueAllocationService)->allocate(999999999999, [['instructor_id' => 1, 'weight' => 500000000000], ['instructor_id' => 2, 'weight' => 500000000000]]);
    expect(array_column($rows, 'amount'))->toBe([500000000000, 499999999999]);
});
test('rejects invalid recipients', function (array $participants) {
    expect(fn () => (new RevenueAllocationService)->allocate(100, $participants))->toThrow(InvalidArgumentException::class);
})->with([[[]], [[['instructor_id' => 1, 'weight' => 0]]], [[['instructor_id' => 1, 'weight' => 1], ['instructor_id' => 1, 'weight' => 2]]]]);
