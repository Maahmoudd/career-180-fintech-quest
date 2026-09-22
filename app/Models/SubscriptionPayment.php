<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Database\Factories\SubscriptionPaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $subscription_id
 * @property Carbon $service_starts_on
 * @property Carbon $service_ends_on
 * @property int|null $platform_fee_bps
 * @property Carbon|null $allocated_at
 * @property Carbon|null $recognized_through
 * @property string $idempotency_key
 * @property string|null $external_reference
 * @property int $amount_minor
 * @property string $currency
 * @property PaymentStatus $status
 * @property Carbon|null $paid_at
 * @property array<string, mixed>|null $metadata
 *
 * @use HasFactory<SubscriptionPaymentFactory>
 */
class SubscriptionPayment extends Model
{
    /** @use HasFactory<SubscriptionPaymentFactory> */
    use HasFactory;

    protected $fillable = ['service_starts_on', 'service_ends_on', 'platform_fee_bps', 'allocated_at', 'recognized_through', 'subscription_id', 'idempotency_key', 'external_reference', 'amount_minor', 'currency', 'status', 'paid_at', 'metadata'];

    protected function casts(): array
    {
        return ['service_starts_on' => 'date', 'service_ends_on' => 'date', 'platform_fee_bps' => 'integer', 'allocated_at' => 'datetime', 'recognized_through' => 'date', 'amount_minor' => 'integer', 'status' => PaymentStatus::class, 'paid_at' => 'datetime', 'metadata' => 'array'];
    }

    /** @return BelongsTo<Subscription, $this> */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /** @return HasMany<RevenueAllocation, $this> */
    public function allocations(): HasMany
    {
        return $this->hasMany(RevenueAllocation::class);
    }

    /** @return HasMany<Refund, $this> */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }
}
