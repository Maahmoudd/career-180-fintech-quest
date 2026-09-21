<?php

namespace App\Domain\Actions;

use App\Models\AuditLog;
use App\Models\EarningLedgerEntry;
use App\Models\InstructorProfile;
use App\Models\RefundAllocation;
use App\Models\SubscriptionPayment;
use App\Support\IntegerMath;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class RecognizeRevenueAction
{
    public function handle(SubscriptionPayment $payment, ?string $through = null): void
    {
        $through ??= now('UTC')->toDateString();
        if ($through > now('UTC')->toDateString()) {
            throw new InvalidArgumentException('Cannot recognize future service.');
        }
        DB::transaction(function () use ($payment, $through): void {
            $payment = SubscriptionPayment::query()->lockForUpdate()->findOrFail($payment->id);
            if ($payment->allocated_at === null) {
                throw new InvalidArgumentException('Allocate the payment first.');
            }
            $totalDays = IntegerMath::days($payment->service_starts_on->toDateString(), $payment->service_ends_on->toDateString());
            $elapsed = max(0, min($totalDays, IntegerMath::days($payment->service_starts_on->toDateString(), $through)));
            foreach ($payment->allocations()->whereNotNull('instructor_profile_id')->orderBy('instructor_profile_id')->get() as $allocation) {
                InstructorProfile::query()->lockForUpdate()->findOrFail($allocation->instructor_profile_id);
                $refunded = (int) RefundAllocation::query()->where('revenue_allocation_id', $allocation->id)->sum('amount_minor');
                $target = min(IntegerMath::proportion($allocation->amount_minor, $elapsed, $totalDays), $allocation->amount_minor - $refunded);
                $recognized = (int) EarningLedgerEntry::query()->where('revenue_allocation_id', $allocation->id)->sum('amount_minor');
                if ($target > $recognized) {
                    $entry = EarningLedgerEntry::create(['instructor_profile_id' => $allocation->instructor_profile_id, 'subscription_payment_id' => $payment->id, 'revenue_allocation_id' => $allocation->id, 'type' => 'earned', 'amount_minor' => $target - $recognized, 'currency' => $payment->currency, 'source_key' => 'recognize:'.$allocation->id.':'.$target, 'effective_at' => $through, 'metadata' => ['cumulative_minor' => $target]]);
                    AuditLog::create(['event' => 'revenue_recognized', 'auditable_type' => EarningLedgerEntry::class, 'auditable_id' => $entry->id, 'after' => ['amount_minor' => $entry->amount_minor]]);
                }
            }
            if ($payment->recognized_through === null || $through > $payment->recognized_through->toDateString()) {
                $payment->update(['recognized_through' => min($through, $payment->service_ends_on->toDateString())]);
            }
        }, 5);
    }
}
