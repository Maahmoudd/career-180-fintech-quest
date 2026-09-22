<?php

namespace App\Models;

use Database\Factories\InstructorProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $payout_account
 * @property string $currency
 * @property bool $active
 * @property-read SubscriptionInstructor $pivot
 *
 * @use HasFactory<InstructorProfileFactory>
 */
class InstructorProfile extends Model
{
    /** @use HasFactory<InstructorProfileFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'payout_account', 'currency', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<RevenueAllocation, $this> */
    public function allocations(): HasMany
    {
        return $this->hasMany(RevenueAllocation::class);
    }

    /** @return HasMany<EarningLedgerEntry, $this> */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(EarningLedgerEntry::class);
    }

    /** @return HasMany<Payout, $this> */
    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class);
    }

    /** @return BelongsToMany<Subscription, $this, SubscriptionInstructor> */
    public function subscriptions(): BelongsToMany
    {
        return $this->belongsToMany(Subscription::class, 'subscription_instructors')->using(SubscriptionInstructor::class)->withPivot(['weight', 'effective_from', 'effective_until'])->withTimestamps();
    }
}
