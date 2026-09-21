<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionInstructor extends Model
{
    use HasFactory;

    protected $fillable = ['subscription_id', 'instructor_profile_id', 'weight', 'effective_from', 'effective_until'];

    protected function casts(): array
    {
        return ['weight' => 'integer', 'effective_from' => 'date', 'effective_until' => 'date'];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(InstructorProfile::class, 'instructor_profile_id');
    }
}
