<?php

namespace App\Models;

use App\Enums\LedgerEntryType;
use Database\Factories\EarningLedgerEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $instructor_profile_id
 * @property int|null $subscription_payment_id
 * @property int|null $revenue_allocation_id
 * @property int|null $refund_id
 * @property int|null $payout_id
 * @property LedgerEntryType $type
 * @property int $amount_minor
 * @property string $currency
 * @property string $source_key
 * @property Carbon $effective_at
 * @property array<string, mixed>|null $metadata
 *
 * @use HasFactory<EarningLedgerEntryFactory>
 */
class EarningLedgerEntry extends Model
{
    /** @use HasFactory<EarningLedgerEntryFactory> */
    use HasFactory;

    protected $fillable = ['instructor_profile_id', 'subscription_payment_id', 'revenue_allocation_id', 'refund_id', 'payout_id', 'type', 'amount_minor', 'currency', 'source_key', 'effective_at', 'metadata'];

    protected function casts(): array
    {
        return ['type' => LedgerEntryType::class, 'amount_minor' => 'integer', 'effective_at' => 'datetime', 'metadata' => 'array'];
    }

    /** @return BelongsTo<InstructorProfile, $this> */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(InstructorProfile::class, 'instructor_profile_id');
    }

    /** @return BelongsTo<SubscriptionPayment, $this> */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPayment::class, 'subscription_payment_id');
    }

    /** @return BelongsTo<RevenueAllocation, $this> */
    public function allocation(): BelongsTo
    {
        return $this->belongsTo(RevenueAllocation::class, 'revenue_allocation_id');
    }

    /** @return BelongsTo<Refund, $this> */
    public function refund(): BelongsTo
    {
        return $this->belongsTo(Refund::class);
    }

    /** @return BelongsTo<Payout, $this> */
    public function payout(): BelongsTo
    {
        return $this->belongsTo(Payout::class);
    }
}
