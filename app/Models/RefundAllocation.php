<?php

namespace App\Models;

use Database\Factories\RefundAllocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $refund_id
 * @property int $revenue_allocation_id
 * @property int|null $instructor_profile_id
 * @property int $amount_minor
 * @property string $currency
 * @property string $allocation_key
 *
 * @use HasFactory<RefundAllocationFactory>
 */
class RefundAllocation extends Model
{
    /** @use HasFactory<RefundAllocationFactory> */
    use HasFactory;

    protected $fillable = ['refund_id', 'revenue_allocation_id', 'instructor_profile_id', 'amount_minor', 'currency', 'allocation_key'];

    protected function casts(): array
    {
        return ['amount_minor' => 'integer'];
    }

    /** @return BelongsTo<Refund, $this> */
    public function refund(): BelongsTo
    {
        return $this->belongsTo(Refund::class);
    }

    /** @return BelongsTo<RevenueAllocation, $this> */
    public function allocation(): BelongsTo
    {
        return $this->belongsTo(RevenueAllocation::class, 'revenue_allocation_id');
    }

    /** @return BelongsTo<InstructorProfile, $this> */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(InstructorProfile::class, 'instructor_profile_id');
    }
}
