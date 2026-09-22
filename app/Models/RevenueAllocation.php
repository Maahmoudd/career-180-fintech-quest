<?php

namespace App\Models;

use Database\Factories\RevenueAllocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $subscription_payment_id
 * @property int|null $instructor_profile_id
 * @property string $recipient_type
 * @property int $amount_minor
 * @property int|null $weight
 * @property int|null $residual_rank
 * @property string $currency
 * @property string $allocation_key
 * @property array<string, mixed>|null $metadata
 *
 * @use HasFactory<RevenueAllocationFactory>
 */
class RevenueAllocation extends Model
{
    /** @use HasFactory<RevenueAllocationFactory> */
    use HasFactory;

    protected $fillable = ['subscription_payment_id', 'instructor_profile_id', 'recipient_type', 'amount_minor', 'weight', 'residual_rank', 'currency', 'allocation_key', 'metadata'];

    protected function casts(): array
    {
        return ['amount_minor' => 'integer', 'weight' => 'integer', 'residual_rank' => 'integer', 'metadata' => 'array'];
    }

    /** @return BelongsTo<SubscriptionPayment, $this> */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPayment::class, 'subscription_payment_id');
    }

    /** @return BelongsTo<InstructorProfile, $this> */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(InstructorProfile::class, 'instructor_profile_id');
    }

    /** @return HasOne<EarningLedgerEntry, $this> */
    public function earning(): HasOne
    {
        return $this->hasOne(EarningLedgerEntry::class);
    }
}
