<?php

namespace Database\Factories;

use App\Models\Payout;
use App\Models\PayoutAttempt;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PayoutAttempt> */
class PayoutAttemptFactory extends Factory
{
    protected $model = PayoutAttempt::class;

    public function definition(): array
    {
        return ['payout_id' => Payout::factory(), 'attempt_number' => 1, 'operation' => 'submit', 'outcome' => 'succeeded', 'provider_reference' => 'mock_'.$this->faker->uuid(), 'idempotency_key' => 'payout:'.$this->faker->uuid(), 'message' => 'Stored provider result.', 'response' => [], 'attempted_at' => now()];
    }
}
