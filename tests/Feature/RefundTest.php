<?php

use App\Domain\Actions\AllocatePaymentAction;
use App\Domain\Actions\ProcessPayoutAction;
use App\Domain\Actions\ProcessRefundAction;
use App\Domain\Actions\RecognizeRevenueAction;
use App\Domain\Services\BalanceService;
use App\Models\EarningLedgerEntry;
use App\Models\RefundAllocation;

test('refunds platform and instructors proportionally and rejects duplicate payload conflicts', function () {
    $this->travelTo(new DateTimeImmutable('2026-01-31 UTC'));
    [$payment] = ledgerPayment();
    app(AllocatePaymentAction::class)->handle($payment);
    $action = app(ProcessRefundAction::class);
    $refund = $action->handle($payment, 5000, 'refund-1');
    $action->handle($payment, 5000, 'refund-1');
    expect((int) RefundAllocation::whereNull('instructor_profile_id')->sum('amount_minor'))->toBe(1000);
    expect((int) RefundAllocation::whereNotNull('instructor_profile_id')->sum('amount_minor'))->toBe(4000);
    expect((int) EarningLedgerEntry::sum('amount_minor'))->toBe(4000);
    $this->assertDatabaseCount('refunds', 1);
    expect(fn () => $action->handle($payment, 1000, 'refund-1'))->toThrow(InvalidArgumentException::class);
    expect(fn () => $action->handle($payment, 5001, 'refund-2'))->toThrow(InvalidArgumentException::class);
});
test('midterm refund removes unearned value before debiting recognized earnings', function () {
    $this->travelTo(new DateTimeImmutable('2026-01-16 UTC'));
    [$payment] = ledgerPayment();
    app(AllocatePaymentAction::class)->handle($payment);
    app(ProcessRefundAction::class)->handle($payment, 5000, 'midterm');
    expect((int) EarningLedgerEntry::where('type', 'refund')->sum('amount_minor'))->toBe(0);
    expect((int) EarningLedgerEntry::sum('amount_minor'))->toBe(4000);
    $this->travelTo(new DateTimeImmutable('2026-01-31 UTC'));
    app(RecognizeRevenueAction::class)->handle($payment);
    expect((int) EarningLedgerEntry::sum('amount_minor'))->toBe(4000);
});
test('many partial refunds never exceed original allocation or lose cents', function () {
    $this->travelTo(new DateTimeImmutable('2026-01-31 UTC'));
    [$payment] = ledgerPayment(101, 3);
    app(AllocatePaymentAction::class)->handle($payment);
    for ($i = 0; $i < 101; $i++) {
        app(ProcessRefundAction::class)->handle($payment, 1, 'cent-'.$i);
    }
    expect((int) RefundAllocation::sum('amount_minor'))->toBe(101);
    expect((int) EarningLedgerEntry::sum('amount_minor'))->toBe(0);
    foreach ($payment->allocations as $allocation) {
        expect((int) RefundAllocation::where('revenue_allocation_id', $allocation->id)->sum('amount_minor'))->toBe($allocation->amount_minor);
    }
});
test('refund after successful payout creates recoverable debt and prevents further payout', function () {
    $this->travelTo(new DateTimeImmutable('2026-01-31 UTC'));
    $path = tempnam(sys_get_temp_dir(), 'quest-refund-');
    config(['services.mock_payments.path' => $path, 'services.mock_payments.mode' => 'success']);
    try {
        [$payment,$instructors] = ledgerPayment();
        app(AllocatePaymentAction::class)->handle($payment);
        app(RecognizeRevenueAction::class)->handle($payment);
        app(ProcessPayoutAction::class)->handle($instructors[0]);
        app(ProcessRefundAction::class)->handle($payment, 10000, 'full');
        expect(app(BalanceService::class)->forInstructor($instructors[0])->outstanding())->toBe(-4000);
        expect(app(ProcessPayoutAction::class)->handle($instructors[0]))->toBeNull();
    } finally {
        unlink($path);
    }
});
