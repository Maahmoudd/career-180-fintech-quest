<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $student_profile_id
 * @property int $subscription_plan_id
 * @property SubscriptionStatus $status
 * @property string $currency
 * @property int $price_minor
 * @property int $platform_fee_bps
 * @property Carbon $starts_on
 * @property Carbon $ends_on
 * @property Carbon|null $cancelled_at
 *
 * @use HasFactory<SubscriptionFactory>
 */
class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory;

    protected $fillable = ['student_profile_id', 'subscription_plan_id', 'status', 'currency', 'price_minor', 'platform_fee_bps', 'starts_on', 'ends_on', 'cancelled_at'];

    protected function casts(): array
    {
        return ['status' => SubscriptionStatus::class, 'price_minor' => 'integer', 'platform_fee_bps' => 'integer', 'starts_on' => 'date', 'ends_on' => 'date', 'cancelled_at' => 'datetime'];
    }

    /** @return BelongsTo<StudentProfile, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }

    /** @return BelongsTo<SubscriptionPlan, $this> */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /** @return BelongsToMany<InstructorProfile, $this, SubscriptionInstructor> */
    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(InstructorProfile::class, 'subscription_instructors')->using(SubscriptionInstructor::class)->withPivot(['weight', 'effective_from', 'effective_until'])->withTimestamps();
    }

    /** @return HasMany<SubscriptionPayment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }
}
