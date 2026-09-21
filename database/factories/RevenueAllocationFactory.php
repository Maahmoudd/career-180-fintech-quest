<?php

namespace Database\Factories;

use App\Models\RevenueAllocation;
use App\Models\SubscriptionPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

class RevenueAllocationFactory extends Factory
{
    protected $model = RevenueAllocation::class;

    public function definition(): array
    {
        return ['subscription_payment_id' => SubscriptionPayment::factory(), 'instructor_profile_id' => null, 'recipient_type' => 'platform', 'amount_minor' => 2000, 'weight' => null, 'residual_rank' => null, 'currency' => 'USD', 'allocation_key' => 'allocation:'.$this->faker->unique()->uuid, 'metadata' => []];
    }
}
