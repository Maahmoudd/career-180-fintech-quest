<?php

namespace App\Models;

use App\Enums\ProviderOutcome;
use Database\Factories\PayoutAttemptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $payout_id
 * @property int $attempt_number
 * @property ProviderOutcome $outcome
 * @property string $operation
 * @property string|null $provider_reference
 * @property string $idempotency_key
 * @property string|null $message
 * @property array<string, mixed>|null $response
 * @property Carbon $attempted_at
 *
 * @use HasFactory<PayoutAttemptFactory>
 */
class PayoutAttempt extends Model
{
    /** @use HasFactory<PayoutAttemptFactory> */
    use HasFactory;

    protected $fillable = ['operation', 'payout_id', 'attempt_number', 'outcome', 'provider_reference', 'idempotency_key', 'message', 'response', 'attempted_at'];

    protected function casts(): array
    {
        return ['outcome' => ProviderOutcome::class, 'attempt_number' => 'integer', 'response' => 'array', 'attempted_at' => 'datetime'];
    }

    /** @return BelongsTo<Payout, $this> */
    public function payout(): BelongsTo
    {
        return $this->belongsTo(Payout::class);
    }
}
