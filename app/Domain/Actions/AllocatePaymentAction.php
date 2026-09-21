<?php

namespace App\Domain\Actions;

use App\Domain\Services\RevenueAllocationService;
use App\Enums\PaymentStatus;
use App\Models\AuditLog;
use App\Models\RevenueAllocation;
use App\Models\SubscriptionPayment;
use App\Support\IntegerMath;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class AllocatePaymentAction
{
    public function __construct(private RevenueAllocationService $allocationService) {}

    public function handle(SubscriptionPayment $payment): void
    {
        DB::transaction(function () use ($payment): void {
            $payment = SubscriptionPayment::query()->lockForUpdate()->findOrFail($payment->id);
            if ($payment->allocated_at !== null) {
                return;
            }
            if ($payment->status !== PaymentStatus::Succeeded || $payment->amount_minor <= 0) {
                throw new InvalidArgumentException('Only positive settled payments can be allocated.');
            }
            $subscription = $payment->subscription()->lockForUpdate()->firstOrFail();
            $start = $payment->service_starts_on ?? $subscription->starts_on;
            $end = $payment->service_ends_on ?? $subscription->ends_on;
            if (IntegerMath::days($start->toDateString(), $end->toDateString()) <= 0 || $payment->currency !== $subscription->currency) {
                throw new InvalidArgumentException('Invalid payment term or currency.');
            }
            $basisPoints = $payment->platform_fee_bps ?? $subscription->platform_fee_bps;
            $fee = (new Money($payment->amount_minor, $payment->currency))->percentage($basisPoints)->minor;
            $instructors = $subscription->instructors()->whereDate('subscription_instructors.effective_from', '<=', $start->toDateString())
                ->where(fn ($query) => $query->whereNull('subscription_instructors.effective_until')->orWhereDate('subscription_instructors.effective_until', '>', $start->toDateString()))->orderBy('instructor_profiles.id')->get();
            $participants = [];
            foreach ($instructors as $instructor) {
                if ($instructor->currency !== $payment->currency) {
                    throw new InvalidArgumentException('Instructor currency mismatch.');
                }
                $participants[] = ['instructor_id' => $instructor->id, 'weight' => (int) $instructor->pivot->weight];
            }
            $rows = $this->allocationService->allocate($payment->amount_minor - $fee, $participants);
            RevenueAllocation::create(['subscription_payment_id' => $payment->id, 'recipient_type' => 'platform', 'amount_minor' => $fee, 'currency' => $payment->currency, 'allocation_key' => 'payment:'.$payment->id.':platform', 'metadata' => ['basis_points' => $basisPoints]]);
            foreach ($rows as $row) {
                RevenueAllocation::create(['subscription_payment_id' => $payment->id, 'instructor_profile_id' => $row['instructor_id'], 'recipient_type' => 'instructor', 'amount_minor' => $row['amount'], 'weight' => $row['weight'], 'residual_rank' => $row['residual_rank'], 'currency' => $payment->currency, 'allocation_key' => 'payment:'.$payment->id.':instructor:'.$row['instructor_id']]);
            }
            $payment->update(['service_starts_on' => $start, 'service_ends_on' => $end, 'platform_fee_bps' => $basisPoints, 'allocated_at' => now()]);
            AuditLog::create(['event' => 'payment_allocated', 'auditable_type' => SubscriptionPayment::class, 'auditable_id' => $payment->id, 'after' => ['amount_minor' => $payment->amount_minor, 'fee_minor' => $fee, 'recognition' => 'daily_utc']]);
        }, 5);
    }
}
