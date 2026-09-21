<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundAllocation extends Model
{
    use HasFactory;

    protected $fillable = ['refund_id', 'revenue_allocation_id', 'instructor_profile_id', 'amount_minor', 'currency', 'allocation_key'];

    protected function casts(): array
    {
        return ['amount_minor' => 'integer'];
    }

    public function refund(): BelongsTo
    {
        return $this->belongsTo(Refund::class);
    }

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(RevenueAllocation::class, 'revenue_allocation_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(InstructorProfile::class, 'instructor_profile_id');
    }
}
