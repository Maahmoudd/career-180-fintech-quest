<?php

namespace App\Domain\Actions;

use App\Domain\Services\RevenueAllocationService;
use App\Models\AuditLog;
use App\Models\EarningLedgerEntry;
use App\Models\InstructorProfile;
use App\Models\Refund;
use App\Models\RefundAllocation;
use App\Models\SubscriptionPayment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class ProcessRefundAction
{
    public function __construct(private RevenueAllocationService $allocationService, private RecognizeRevenueAction $recognition) {}

    /** Record a confirmed incoming refund event; this operation does not initiate a second customer refund. */
    public function handle(SubscriptionPayment $payment, int $amountMinor, string $idempotencyKey, string $reason = 'customer_request'): Refund
    {
        if ($amountMinor <= 0 || $idempotencyKey === '' || strlen($idempotencyKey) > 150) {
            throw new InvalidArgumentException('Positive refund and a bounded idempotency key required.');
        }

        return DB::transaction(function () use ($payment, $amountMinor, $idempotencyKey, $reason): Refund {
            $payment = SubscriptionPayment::query()->lockForUpdate()->findOrFail($payment->id);
            $existing = Refund::query()->where('idempotency_key', $idempotencyKey)->first();
            if ($existing !== null) {
                if ($existing->subscription_payment_id !== $payment->id || $existing->amount_minor !== $amountMinor || $existing->reason !== $reason) {
                    throw new InvalidArgumentException('Idempotency key payload conflict.');
                }

                return $existing;
            }
            if ($payment->allocated_at === null) {
                throw new InvalidArgumentException('Allocate payment before recording a refund.');
            }
            $this->recognition->handle($payment);
            $refunded = (int) $payment->refunds()->where('status', 'succeeded')->sum('amount_minor');
            if ($refunded + $amountMinor > $payment->amount_minor) {
                throw new InvalidArgumentException('Refund exceeds remaining payment.');
            }
            $refund = $payment->refunds()->create(['idempotency_key' => $idempotencyKey, 'amount_minor' => $amountMinor, 'currency' => $payment->currency, 'status' => 'succeeded', 'reason' => $reason, 'refunded_at' => now()]);
            $allocations = $payment->allocations()->orderBy('id')->get()->keyBy('id');
            $participants = [];
            foreach ($allocations as $allocation) {
                $capacity = $allocation->amount_minor - (int) RefundAllocation::query()->where('revenue_allocation_id', $allocation->id)->sum('amount_minor');
                if ($capacity > 0) {
                    $participants[] = ['instructor_id' => $allocation->id, 'weight' => $capacity];
                }
            }
            $rows = $this->allocationService->allocate($amountMinor, $participants);
            usort($rows, fn (array $a, array $b): int => ($allocations[$a['instructor_id']]->instructor_profile_id ?? 0) <=> ($allocations[$b['instructor_id']]->instructor_profile_id ?? 0));
            foreach ($rows as $row) {
                $allocation = $allocations[$row['instructor_id']];
                $share = $row['amount'];
                RefundAllocation::create(['refund_id' => $refund->id, 'revenue_allocation_id' => $allocation->id, 'instructor_profile_id' => $allocation->instructor_profile_id, 'amount_minor' => $share, 'currency' => $payment->currency, 'allocation_key' => 'refund:'.$refund->id.':allocation:'.$allocation->id]);
                if ($allocation->instructor_profile_id === null) {
                    continue;
                }
                InstructorProfile::query()->lockForUpdate()->findOrFail($allocation->instructor_profile_id);
                $remaining = $allocation->amount_minor - (int) RefundAllocation::query()->where('revenue_allocation_id', $allocation->id)->sum('amount_minor');
                $recognized = (int) EarningLedgerEntry::query()->where('revenue_allocation_id', $allocation->id)->sum('amount_minor');
                $debit = max(0, $recognized - $remaining);
                if ($debit > 0) {
                    EarningLedgerEntry::create(['instructor_profile_id' => $allocation->instructor_profile_id, 'subscription_payment_id' => $payment->id, 'revenue_allocation_id' => $allocation->id, 'refund_id' => $refund->id, 'type' => 'refund', 'amount_minor' => -$debit, 'currency' => $payment->currency, 'source_key' => 'refund-ledger:'.$refund->id.':'.$allocation->id, 'effective_at' => now()]);
                }
            }
            $payment->update(['status' => $refunded + $amountMinor === $payment->amount_minor ? 'refunded' : 'partially_refunded']);
            AuditLog::create(['event' => 'refund_processed', 'auditable_type' => Refund::class, 'auditable_id' => $refund->id, 'after' => ['amount_minor' => $amountMinor, 'payment_id' => $payment->id, 'reason' => $reason]]);

            return $refund;
        }, 5);
    }
}
