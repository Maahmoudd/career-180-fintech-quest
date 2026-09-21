<?php

namespace App\Domain\Actions;

use App\Enums\SubscriptionStatus;
use App\Models\AuditLog;
use App\Models\PlanChange;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Support\IntegerMath;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class ChangeSubscriptionPlanAction
{
    public function handle(Subscription $subscription, SubscriptionPlan $newPlan, \DateTimeInterface $effectiveOn, string $idempotencyKey): PlanChange
    {
        return DB::transaction(function () use ($subscription, $newPlan, $effectiveOn, $idempotencyKey): PlanChange {
            $subscription = Subscription::query()->lockForUpdate()->findOrFail($subscription->id);
            $existing = PlanChange::query()->where('idempotency_key', $idempotencyKey)->first();
            if ($existing !== null) {
                return $existing;
            }
            if ($subscription->status !== SubscriptionStatus::Active) {
                throw new InvalidArgumentException('Only active subscriptions can change plans.');
            }
            $effective = (new \DateTimeImmutable($effectiveOn->format('Y-m-d')));
            if ($effective < new \DateTimeImmutable($subscription->starts_on->format('Y-m-d')) || $effective >= new \DateTimeImmutable($subscription->ends_on->format('Y-m-d'))) {
                throw new InvalidArgumentException('Effective date must be inside the current term.');
            }
            if ($newPlan->currency !== $subscription->currency) {
                throw new InvalidArgumentException('Plan currency cannot change mid-term.');
            }
            $totalDays = max(1, IntegerMath::days($subscription->starts_on->toDateString(), $subscription->ends_on->toDateString()));
            $remainingDays = max(0, IntegerMath::days($effective->format('Y-m-d'), $subscription->ends_on->toDateString()));
            $oldRemaining = IntegerMath::proportion($subscription->price_minor, $remainingDays, $totalDays);
            $newRemaining = IntegerMath::proportion($newPlan->price_minor, $remainingDays, $newPlan->term_days);
            $adjustment = $newRemaining - $oldRemaining;
            $change = PlanChange::create(['subscription_id' => $subscription->id, 'old_plan_id' => $subscription->subscription_plan_id, 'new_plan_id' => $newPlan->id, 'idempotency_key' => $idempotencyKey, 'request_hash' => hash('sha256', json_encode([$subscription->id, $newPlan->id, $effective->format('Y-m-d')], JSON_THROW_ON_ERROR)), 'effective_on' => $effective->format('Y-m-d'), 'adjustment_minor' => $adjustment, 'credit_minor' => max(0, -$adjustment), 'replacement_minor' => max(0, $adjustment), 'metadata' => ['remaining_days' => $remainingDays, 'total_days' => $totalDays]]);
            $subscription->update(['subscription_plan_id' => $newPlan->id, 'price_minor' => $newPlan->price_minor, 'platform_fee_bps' => $newPlan->platform_fee_bps]);
            AuditLog::create(['event' => 'subscription_plan_changed', 'auditable_type' => Subscription::class, 'auditable_id' => $subscription->id, 'after' => ['plan_change_id' => $change->id, 'adjustment_minor' => $adjustment, 'effective_on' => $effective->format('Y-m-d')]]);

            return $change;
        }, 5);
    }
}
