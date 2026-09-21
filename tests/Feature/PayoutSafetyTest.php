<?php

use App\Domain\Actions\ProcessPayoutAction;
use App\Domain\Contracts\PaymentProvider;
use App\Domain\Providers\MockPaymentProvider;
use App\Domain\Providers\ProviderResult;
use App\Domain\Services\BalanceService;
use App\Enums\PayoutStatus;
use App\Enums\ProviderOutcome;
use App\Jobs\ProcessPayoutJob;
use App\Models\EarningLedgerEntry;
use App\Models\InstructorProfile;
use App\Models\Payout;

beforeEach(function () {
    config(['services.mock_payments.path' => tempnam(sys_get_temp_dir(), 'quest-provider-'), 'services.mock_payments.mode' => 'success']);
    $this->freezeTime();
});
afterEach(function () {
    @unlink(config('services.mock_payments.path'));
});
test('repeated payout processing and duplicated jobs move money only once', function () {
    $instructor = InstructorProfile::factory()->create();
    EarningLedgerEntry::factory()->for($instructor, 'instructor')->create(['amount_minor' => 1000]);
    $action = app(ProcessPayoutAction::class);
    (new ProcessPayoutJob($instructor->id))->handle($action);
    (new ProcessPayoutJob($instructor->id))->handle($action);
    $action->handle($instructor);
    $this->assertDatabaseCount('payouts', 1);
    $this->assertDatabaseHas('payouts', ['status' => 'succeeded', 'amount_minor' => 1000]);
    expect((new MockPaymentProvider)->successfulTransfers())->toBe(1);
    expect(app(BalanceService::class)->forInstructor($instructor)->outstanding())->toBe(0);
});
test('reconciles timeout after success without a returned reference or a second transfer', function () {
    config(['services.mock_payments.mode' => 'timeout_after_success']);
    $instructor = InstructorProfile::factory()->create();
    EarningLedgerEntry::factory()->for($instructor, 'instructor')->create();
    $payout = app(ProcessPayoutAction::class)->handle($instructor);
    expect($payout->status)->toBe(PayoutStatus::Unknown);
    expect($payout->provider_reference)->toBeNull();
    expect(app(BalanceService::class)->forInstructor($instructor)->paid)->toBe(0);
    $this->travel(61)->seconds();
    $resolved = app(ProcessPayoutAction::class)->handle($instructor);
    expect($resolved->id)->toBe($payout->id);
    expect($resolved->status)->toBe(PayoutStatus::Succeeded);
    expect((new MockPaymentProvider)->successfulTransfers())->toBe(1);
    $this->assertDatabaseCount('payouts', 1);
});
test('recovers a crash after the provider committed but before local confirmation', function () {
    $instructor = InstructorProfile::factory()->create();
    EarningLedgerEntry::factory()->for($instructor, 'instructor')->create();
    $payout = app(ProcessPayoutAction::class)->reserve($instructor);
    $payout->update(['status' => 'submitting', 'next_attempt_at' => now()->addSeconds(90)]);
    app(PaymentProvider::class)->sendPayout($payout->destination, $payout->amount_minor, $payout->currency, $payout->idempotency_key);
    $this->travel(91)->seconds();
    expect(app(ProcessPayoutAction::class)->submit($payout)->status)->toBe(PayoutStatus::Succeeded);
    expect((new MockPaymentProvider)->successfulTransfers())->toBe(1);
});
test('retains reservations through delayed confirmation', function () {
    config(['services.mock_payments.mode' => 'delayed_confirmation']);
    $instructor = InstructorProfile::factory()->create();
    EarningLedgerEntry::factory()->for($instructor, 'instructor')->create();
    $action = app(ProcessPayoutAction::class);
    $payout = $action->handle($instructor);
    for ($i = 0; $i < 2; $i++) {
        $this->travel(61)->seconds();
        expect($action->submit($payout)->status)->toBe(PayoutStatus::Unknown);
    }
    expect(app(BalanceService::class)->forInstructor($instructor)->available())->toBe(0);
    $this->travel(61)->seconds();
    expect($action->submit($payout)->status)->toBe(PayoutStatus::Succeeded);
    expect((new MockPaymentProvider)->successfulTransfers())->toBe(1);
});
test('retries a request not accepted by the provider using the original key', function (string $mode) {
    config(['services.mock_payments.mode' => $mode]);
    $instructor = InstructorProfile::factory()->create();
    EarningLedgerEntry::factory()->for($instructor, 'instructor')->create();
    $payout = app(ProcessPayoutAction::class)->handle($instructor);
    config(['services.mock_payments.mode' => 'success']);
    $this->travel(61)->seconds();
    app(ProcessPayoutAction::class)->submit($payout);
    $this->travel(61)->seconds();
    $result = app(ProcessPayoutAction::class)->submit($payout);
    expect($result->status)->toBe(PayoutStatus::Succeeded);
    expect($result->idempotency_key)->toBe($payout->idempotency_key);
    $this->assertDatabaseCount('payouts', 1);
})->with(['timeout_before_processing', 'temporary_failure']);
test('permanent rejection blocks automatic new payout creation and never marks paid', function () {
    config(['services.mock_payments.mode' => 'permanent_failure']);
    $instructor = InstructorProfile::factory()->create();
    EarningLedgerEntry::factory()->for($instructor, 'instructor')->create();
    $action = app(ProcessPayoutAction::class);
    $payout = $action->handle($instructor);
    $action->handle($instructor);
    expect($payout->status)->toBe(PayoutStatus::FailedPermanent);
    expect(app(BalanceService::class)->forInstructor($instructor)->paid)->toBe(0);
    $this->assertDatabaseCount('payouts', 1);
    expect((new MockPaymentProvider)->successfulTransfers())->toBe(0);
});
test('provider rejects reused key with a different financial payload', function () {
    $provider = new MockPaymentProvider;
    $provider->sendPayout('account', 1000, 'USD', 'stable-key');
    expect(fn () => (new MockPaymentProvider)->sendPayout('account', 2000, 'USD', 'stable-key'))->toThrow(InvalidArgumentException::class);
    expect($provider->successfulTransfers())->toBe(1);
});
test('rejects applying a success to an unsubmitted payout', function () {
    $payout = Payout::factory()->create();
    expect(fn () => app(ProcessPayoutAction::class)->applyResult($payout, new ProviderResult(ProviderOutcome::Succeeded, 'fake')))->toThrow(LogicException::class);
});
