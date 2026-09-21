<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RevenueAllocation extends Model
{
    use HasFactory;

    protected $fillable = ['subscription_payment_id', 'instructor_profile_id', 'recipient_type', 'amount_minor', 'weight', 'residual_rank', 'currency', 'allocation_key', 'metadata'];

    protected function casts(): array
    {
        return ['amount_minor' => 'integer', 'weight' => 'integer', 'residual_rank' => 'integer', 'metadata' => 'array'];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPayment::class, 'subscription_payment_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(InstructorProfile::class, 'instructor_profile_id');
    }

    public function earning(): HasOne
    {
        return $this->hasOne(EarningLedgerEntry::class);
    }
}
