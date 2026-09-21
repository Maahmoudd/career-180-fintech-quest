<?php

namespace Database\Factories;

use App\Models\Refund;
use App\Models\RefundAllocation;
use App\Models\RevenueAllocation;
use Illuminate\Database\Eloquent\Factories\Factory;

class RefundAllocationFactory extends Factory
{
    protected $model = RefundAllocation::class;

    public function definition(): array
    {
        return ['refund_id' => Refund::factory(), 'revenue_allocation_id' => RevenueAllocation::factory(), 'instructor_profile_id' => null, 'amount_minor' => 100, 'currency' => 'USD', 'allocation_key' => 'refund-allocation:'.$this->faker->unique()->uuid()];
    }
}
