<?php

namespace Database\Factories;

use App\Models\InstructorProfile;
use App\Models\Subscription;
use App\Models\SubscriptionInstructor;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SubscriptionInstructor> */
class SubscriptionInstructorFactory extends Factory
{
    protected $model = SubscriptionInstructor::class;

    public function definition(): array
    {
        return ['subscription_id' => Subscription::factory(), 'instructor_profile_id' => InstructorProfile::factory(), 'weight' => 1, 'effective_from' => now()->toDateString(), 'effective_until' => null];
    }
}
