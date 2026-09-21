<?php

namespace App\Models;

use App\Enums\RefundStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = ['subscription_payment_id', 'idempotency_key', 'amount_minor', 'currency', 'status', 'reason', 'refunded_at'];

    protected function casts(): array
    {
        return ['amount_minor' => 'integer', 'status' => RefundStatus::class, 'refunded_at' => 'datetime'];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPayment::class, 'subscription_payment_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(RefundAllocation::class);
    }
}
