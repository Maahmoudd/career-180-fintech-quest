<?php

namespace Database\Factories;

use App\Enums\PayoutStatus;
use App\Models\InstructorProfile;
use App\Models\Payout;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Payout> */
class PayoutFactory extends Factory
{
    protected $model = Payout::class;

    public function definition(): array
    {
        return ['instructor_profile_id' => InstructorProfile::factory(), 'idempotency_key' => 'payout:'.$this->faker->unique()->uuid, 'status' => PayoutStatus::Pending, 'amount_minor' => 1000, 'currency' => 'USD', 'provider_reference' => null, 'failure_code' => null, 'failure_message' => null, 'submitted_at' => null, 'completed_at' => null];
    }
}
