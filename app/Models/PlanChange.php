<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanChange extends Model
{
    use HasFactory;

    protected $fillable = ['subscription_id', 'old_plan_id', 'new_plan_id', 'idempotency_key', 'request_hash', 'effective_on', 'adjustment_minor', 'credit_minor', 'replacement_minor', 'metadata'];

    protected function casts(): array
    {
        return ['effective_on' => 'date', 'adjustment_minor' => 'integer', 'credit_minor' => 'integer', 'replacement_minor' => 'integer', 'metadata' => 'array'];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function oldPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'old_plan_id');
    }

    public function newPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'new_plan_id');
    }
}
