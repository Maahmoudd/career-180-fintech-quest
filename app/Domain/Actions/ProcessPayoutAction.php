<?php

namespace App\Domain\Actions;

use App\Domain\Contracts\PaymentProvider;
use App\Domain\Providers\ProviderResult;
use App\Domain\Services\BalanceService;
use App\Enums\PayoutStatus;
use App\Enums\ProviderOutcome;
use App\Models\AuditLog;
use App\Models\EarningLedgerEntry;
use App\Models\InstructorProfile;
use App\Models\Payout;
use App\Support\IntegerMath;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LogicException;
use Throwable;

final class ProcessPayoutAction
{
    public function __construct(private PaymentProvider $provider, private BalanceService $balances) {}

    public function reserve(InstructorProfile $instructor): ?Payout
    {
        return DB::transaction(function () use ($instructor): ?Payout {
            $instructor = InstructorProfile::query()->lockForUpdate()->findOrFail($instructor->id);
            $existing = $instructor->payouts()->whereNotNull('active_instructor_id')->first();
            if ($existing !== null) {
                return $existing;
            }
            if (! $instructor->active || empty($instructor->payout_account)) {
                return null;
            }
            $amount = min(IntegerMath::MAX_AMOUNT, $this->balances->outstanding($instructor));
            if ($amount <= 0) {
                return null;
            }
            $payout = $instructor->payouts()->create(['idempotency_key' => 'payout:'.Str::uuid(), 'status' => PayoutStatus::Reserved, 'amount_minor' => $amount, 'currency' => $instructor->currency, 'destination' => $instructor->payout_account, 'active_instructor_id' => $instructor->id, 'ledger_cutoff_id' => $instructor->ledgerEntries()->max('id')]);
            AuditLog::create(['event' => 'payout_reserved', 'auditable_type' => Payout::class, 'auditable_id' => $payout->id, 'after' => ['amount_minor' => $amount, 'ledger_cutoff_id' => $payout->ledger_cutoff_id]]);

            return $payout;
        }, 5);
    }

    public function handle(InstructorProfile $instructor): ?Payout
    {
        $payout = $this->reserve($instructor);

        return $payout === null ? null : $this->submit($payout);
    }

    public function submit(Payout $payout): Payout
    {
        $operation = DB::transaction(function () use ($payout): ?string {
            $locked = Payout::query()->lockForUpdate()->findOrFail($payout->id);
            if (in_array($locked->status, [PayoutStatus::Succeeded, PayoutStatus::FailedPermanent], true)) {
                return null;
            }
            if ($locked->next_attempt_at !== null && $locked->next_attempt_at->isFuture()) {
                return null;
            }
            $operation = in_array($locked->status, [PayoutStatus::Submitting, PayoutStatus::Unknown, PayoutStatus::Reconciling], true) ? 'lookup' : 'submit';
            $locked->update(['status' => $operation === 'lookup' ? PayoutStatus::Reconciling : PayoutStatus::Submitting, 'submitted_at' => $locked->submitted_at ?? now(), 'next_attempt_at' => now()->addSeconds(90)]);
            $this->attempt($locked, $operation.':started', new ProviderResult(ProviderOutcome::Unknown, null, 'Durable intent before provider call.'));

            return $operation;
        }, 5);
        if ($operation === null) {
            return $payout->refresh();
        }
        $payout->refresh();
        try {
            $result = $operation === 'lookup'
                ? $this->provider->payoutStatus($payout->provider_reference, $payout->idempotency_key)
                : $this->provider->sendPayout($payout->destination, $payout->amount_minor, $payout->currency, $payout->idempotency_key);
        } catch (Throwable $exception) {
            report($exception);
            $result = new ProviderResult(ProviderOutcome::Unknown, null, 'Provider transport error; reconciliation required.');
        }

        return $this->applyResult($payout, $result, $operation.':result');
    }

    public function applyResult(Payout $payout, ProviderResult $result, string $operation = 'lookup:result'): Payout
    {
        return DB::transaction(function () use ($payout, $result, $operation): Payout {
            InstructorProfile::query()->lockForUpdate()->findOrFail($payout->instructor_profile_id);
            $payout = Payout::query()->lockForUpdate()->findOrFail($payout->id);
            if ($payout->status === PayoutStatus::Succeeded) {
                return $payout;
            }
            if (! in_array($payout->status, [PayoutStatus::Submitting, PayoutStatus::Unknown, PayoutStatus::Reconciling], true)) {
                throw new LogicException('Provider result is invalid for this payout state.');
            }
            if ($result->outcome === ProviderOutcome::Succeeded && $result->providerReference === null) {
                throw new LogicException('Success requires a persistent provider reference.');
            }
            $this->attempt($payout, $operation, $result);
            $status = match ($result->outcome) {
                ProviderOutcome::Succeeded => PayoutStatus::Succeeded,
                ProviderOutcome::FailedPermanent => PayoutStatus::FailedPermanent,
                ProviderOutcome::FailedTemporary => PayoutStatus::FailedRetryable,
                ProviderOutcome::Unknown => PayoutStatus::Unknown,
            };
            if ($status === PayoutStatus::Succeeded) {
                EarningLedgerEntry::create(['instructor_profile_id' => $payout->instructor_profile_id, 'payout_id' => $payout->id, 'type' => 'payout', 'amount_minor' => -$payout->amount_minor, 'currency' => $payout->currency, 'source_key' => 'payout:'.$payout->id, 'effective_at' => now()]);
            }
            $payout->update(['status' => $status, 'provider_reference' => $result->providerReference ?? $payout->provider_reference, 'failure_message' => $status === PayoutStatus::Succeeded ? null : $result->message, 'completed_at' => $status === PayoutStatus::Succeeded ? now() : null, 'active_instructor_id' => $status === PayoutStatus::Succeeded ? null : $payout->instructor_profile_id, 'next_attempt_at' => in_array($status, [PayoutStatus::Unknown, PayoutStatus::FailedRetryable], true) ? now()->addSeconds(60) : null]);
            AuditLog::create(['event' => 'payout_'.$status->value, 'auditable_type' => Payout::class, 'auditable_id' => $payout->id, 'after' => ['status' => $status->value, 'provider_reference' => $payout->provider_reference]]);

            return $payout;
        }, 5);
    }

    private function attempt(Payout $payout, string $operation, ProviderResult $result): void
    {
        $payout->attempts()->create(['attempt_number' => 1 + (int) $payout->attempts()->max('attempt_number'), 'operation' => $operation, 'outcome' => $result->outcome, 'provider_reference' => $result->providerReference, 'idempotency_key' => $payout->idempotency_key, 'message' => $result->message, 'response' => $result->payload, 'attempted_at' => now()]);
    }
}
