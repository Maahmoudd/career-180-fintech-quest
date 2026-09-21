<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPayment extends Model
{
    use HasFactory;

    protected $fillable = ['service_starts_on', 'service_ends_on', 'platform_fee_bps', 'allocated_at', 'recognized_through', 'subscription_id', 'idempotency_key', 'external_reference', 'amount_minor', 'currency', 'status', 'paid_at', 'metadata'];

    protected function casts(): array
    {
        return ['service_starts_on' => 'date', 'service_ends_on' => 'date', 'platform_fee_bps' => 'integer', 'allocated_at' => 'datetime', 'recognized_through' => 'date', 'amount_minor' => 'integer', 'status' => PaymentStatus::class, 'paid_at' => 'datetime', 'metadata' => 'array'];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(RevenueAllocation::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }
}
