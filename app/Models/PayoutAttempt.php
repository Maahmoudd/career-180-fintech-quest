<?php

namespace App\Models;

use App\Enums\ProviderOutcome;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutAttempt extends Model
{
    use HasFactory;

    protected $fillable = ['operation', 'payout_id', 'attempt_number', 'outcome', 'provider_reference', 'idempotency_key', 'message', 'response', 'attempted_at'];

    protected function casts(): array
    {
        return ['outcome' => ProviderOutcome::class, 'attempt_number' => 'integer', 'response' => 'array', 'attempted_at' => 'datetime'];
    }

    public function payout(): BelongsTo
    {
        return $this->belongsTo(Payout::class);
    }
}
