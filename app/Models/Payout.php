<?php

namespace App\Models;

use App\Enums\PayoutStatus;
use Database\Factories\PayoutFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $instructor_profile_id
 * @property int|null $active_instructor_id
 * @property int|null $ledger_cutoff_id
 * @property Carbon|null $next_attempt_at
 * @property string $destination
 * @property string $idempotency_key
 * @property PayoutStatus $status
 * @property int $amount_minor
 * @property string $currency
 * @property string|null $provider_reference
 * @property string|null $failure_code
 * @property string|null $failure_message
 * @property Carbon|null $submitted_at
 * @property Carbon|null $completed_at
 *
 * @use HasFactory<PayoutFactory>
 */
class Payout extends Model
{
    /** @use HasFactory<PayoutFactory> */
    use HasFactory;

    protected $fillable = ['destination', 'active_instructor_id', 'ledger_cutoff_id', 'next_attempt_at', 'instructor_profile_id', 'idempotency_key', 'status', 'amount_minor', 'currency', 'provider_reference', 'failure_code', 'failure_message', 'submitted_at', 'completed_at'];

    protected function casts(): array
    {
        return ['active_instructor_id' => 'integer', 'ledger_cutoff_id' => 'integer', 'next_attempt_at' => 'datetime', 'status' => PayoutStatus::class, 'amount_minor' => 'integer', 'submitted_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    /** @return BelongsTo<InstructorProfile, $this> */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(InstructorProfile::class, 'instructor_profile_id');
    }

    /** @return HasMany<PayoutAttempt, $this> */
    public function attempts(): HasMany
    {
        return $this->hasMany(PayoutAttempt::class);
    }

    /** @return HasMany<EarningLedgerEntry, $this> */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(EarningLedgerEntry::class);
    }
}
