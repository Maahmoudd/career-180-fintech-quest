<?php

namespace App\Models;

use App\Enums\RefundStatus;
use Database\Factories\RefundFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $subscription_payment_id
 * @property string $idempotency_key
 * @property int $amount_minor
 * @property string $currency
 * @property RefundStatus $status
 * @property string $reason
 * @property Carbon|null $refunded_at
 *
 * @use HasFactory<RefundFactory>
 */
class Refund extends Model
{
    /** @use HasFactory<RefundFactory> */
    use HasFactory;

    protected $fillable = ['subscription_payment_id', 'idempotency_key', 'amount_minor', 'currency', 'status', 'reason', 'refunded_at'];

    protected function casts(): array
    {
        return ['amount_minor' => 'integer', 'status' => RefundStatus::class, 'refunded_at' => 'datetime'];
    }

    /** @return BelongsTo<SubscriptionPayment, $this> */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPayment::class, 'subscription_payment_id');
    }

    /** @return HasMany<RefundAllocation, $this> */
    public function allocations(): HasMany
    {
        return $this->hasMany(RefundAllocation::class);
    }
}
