<?php

use App\Domain\Actions\AllocatePaymentAction;
use App\Domain\Actions\RecognizeRevenueAction;
use App\Models\AuditLog;
use App\Models\EarningLedgerEntry;
use App\Models\RevenueAllocation;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

test('allocates once and recognizes only completed service days', function () {
    $this->travelTo(new DateTimeImmutable('2026-01-16 00:00:00 UTC'));
    [$payment,$instructors] = ledgerPayment();
    app(AllocatePaymentAction::class)->handle($payment);
    app(AllocatePaymentAction::class)->handle($payment);
    $this->assertDatabaseCount('revenue_allocations', 3);
    $this->assertDatabaseCount('earning_ledger_entries', 0);
    expect((int) RevenueAllocation::sum('amount_minor'))->toBe(10000);
    app(RecognizeRevenueAction::class)->handle($payment);
    app(RecognizeRevenueAction::class)->handle($payment);
    expect((int) EarningLedgerEntry::sum('amount_minor'))->toBe(4000);
    $this->travelTo(new DateTimeImmutable('2026-01-31 UTC'));
    app(RecognizeRevenueAction::class)->handle($payment);
    expect((int) EarningLedgerEntry::sum('amount_minor'))->toBe(8000);
    $this->assertDatabaseHas('audit_logs', ['event' => 'revenue_recognized']);
});
test('rolls back allocation when participants are missing', function () {
    [$payment] = ledgerPayment(10000, 0);
    expect(fn () => app(AllocatePaymentAction::class)->handle($payment))->toThrow(InvalidArgumentException::class);
    $this->assertDatabaseCount('revenue_allocations', 0);
    expect($payment->refresh()->allocated_at)->toBeNull();
});
test('keeps snapshots independent of later plan edits', function () {
    $this->travelTo(new DateTimeImmutable('2026-01-31 UTC'));
    [$payment] = ledgerPayment();
    app(AllocatePaymentAction::class)->handle($payment);
    $payment->subscription->update(['platform_fee_bps' => 5000, 'ends_on' => '2027-01-01']);
    app(RecognizeRevenueAction::class)->handle($payment);
    expect((int) EarningLedgerEntry::sum('amount_minor'))->toBe(8000);
});
test('rejects direct mutation and deletion of financial history', function () {
    [$payment] = ledgerPayment();
    app(AllocatePaymentAction::class)->handle($payment);
    expect(fn () => DB::table('revenue_allocations')->update(['amount_minor' => 1]))->toThrow(QueryException::class);
    expect(fn () => AuditLog::query()->delete())->toThrow(QueryException::class);
});
test('does not recognize future service', function () {
    $this->travelTo(new DateTimeImmutable('2026-01-01 UTC'));
    [$payment] = ledgerPayment();
    app(AllocatePaymentAction::class)->handle($payment);
    expect(fn () => app(RecognizeRevenueAction::class)->handle($payment, '2026-02-01'))->toThrow(InvalidArgumentException::class);
    $this->assertDatabaseCount('earning_ledger_entries', 0);
});
