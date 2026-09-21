<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = ['student_profile_id', 'subscription_plan_id', 'status', 'currency', 'price_minor', 'platform_fee_bps', 'starts_on', 'ends_on', 'cancelled_at'];

    protected function casts(): array
    {
        return ['status' => SubscriptionStatus::class, 'price_minor' => 'integer', 'platform_fee_bps' => 'integer', 'starts_on' => 'date', 'ends_on' => 'date', 'cancelled_at' => 'datetime'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(InstructorProfile::class, 'subscription_instructors')->withPivot(['weight', 'effective_from', 'effective_until'])->withTimestamps();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }
}
