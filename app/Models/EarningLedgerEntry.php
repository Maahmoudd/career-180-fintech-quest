<?php

namespace App\Models;

use App\Enums\LedgerEntryType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EarningLedgerEntry extends Model
{
    use HasFactory;

    protected $fillable = ['instructor_profile_id', 'subscription_payment_id', 'revenue_allocation_id', 'refund_id', 'payout_id', 'type', 'amount_minor', 'currency', 'source_key', 'effective_at', 'metadata'];

    protected function casts(): array
    {
        return ['type' => LedgerEntryType::class, 'amount_minor' => 'integer', 'effective_at' => 'datetime', 'metadata' => 'array'];
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(InstructorProfile::class, 'instructor_profile_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPayment::class, 'subscription_payment_id');
    }

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(RevenueAllocation::class, 'revenue_allocation_id');
    }

    public function refund(): BelongsTo
    {
        return $this->belongsTo(Refund::class);
    }

    public function payout(): BelongsTo
    {
        return $this->belongsTo(Payout::class);
    }
}
