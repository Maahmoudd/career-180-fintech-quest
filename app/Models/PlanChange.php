<?php

namespace App\Models;

use Database\Factories\PlanChangeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $subscription_id
 * @property int $old_plan_id
 * @property int $new_plan_id
 * @property string $idempotency_key
 * @property string $request_hash
 * @property Carbon $effective_on
 * @property int $adjustment_minor
 * @property int $credit_minor
 * @property int $replacement_minor
 * @property array<string, mixed>|null $metadata
 *
 * @use HasFactory<PlanChangeFactory>
 */
class PlanChange extends Model
{
    /** @use HasFactory<PlanChangeFactory> */
    use HasFactory;

    protected $fillable = ['subscription_id', 'old_plan_id', 'new_plan_id', 'idempotency_key', 'request_hash', 'effective_on', 'adjustment_minor', 'credit_minor', 'replacement_minor', 'metadata'];

    protected function casts(): array
    {
        return ['effective_on' => 'date', 'adjustment_minor' => 'integer', 'credit_minor' => 'integer', 'replacement_minor' => 'integer', 'metadata' => 'array'];
    }

    /** @return BelongsTo<Subscription, $this> */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /** @return BelongsTo<SubscriptionPlan, $this> */
    public function oldPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'old_plan_id');
    }

    /** @return BelongsTo<SubscriptionPlan, $this> */
    public function newPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'new_plan_id');
    }
}
