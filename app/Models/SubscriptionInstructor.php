<?php

namespace App\Models;

use Database\Factories\SubscriptionInstructorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $subscription_id
 * @property int $instructor_profile_id
 * @property int $weight
 * @property Carbon $effective_from
 * @property Carbon|null $effective_until
 *
 * @use HasFactory<SubscriptionInstructorFactory>
 */
class SubscriptionInstructor extends Pivot
{
    /** @use HasFactory<SubscriptionInstructorFactory> */
    use HasFactory;

    public $incrementing = true;

    protected $fillable = ['subscription_id', 'instructor_profile_id', 'weight', 'effective_from', 'effective_until'];

    protected function casts(): array
    {
        return ['weight' => 'integer', 'effective_from' => 'date', 'effective_until' => 'date'];
    }

    /** @return BelongsTo<Subscription, $this> */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /** @return BelongsTo<InstructorProfile, $this> */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(InstructorProfile::class, 'instructor_profile_id');
    }
}
