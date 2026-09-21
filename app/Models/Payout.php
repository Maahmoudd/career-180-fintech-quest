<?php

namespace App\Models;

use App\Enums\PayoutStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payout extends Model
{
    use HasFactory;

    protected $fillable = ['destination', 'active_instructor_id', 'ledger_cutoff_id', 'next_attempt_at', 'instructor_profile_id', 'idempotency_key', 'status', 'amount_minor', 'currency', 'provider_reference', 'failure_code', 'failure_message', 'submitted_at', 'completed_at'];

    protected function casts(): array
    {
        return ['active_instructor_id' => 'integer', 'ledger_cutoff_id' => 'integer', 'next_attempt_at' => 'datetime', 'status' => PayoutStatus::class, 'amount_minor' => 'integer', 'submitted_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(InstructorProfile::class, 'instructor_profile_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(PayoutAttempt::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(EarningLedgerEntry::class);
    }
}
