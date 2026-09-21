<?php

namespace Database\Factories;

use App\Enums\PlanInterval;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;

    public function definition(): array
    {
        return ['name' => 'Monthly', 'interval' => PlanInterval::Monthly, 'term_days' => 30, 'price_minor' => 10000, 'platform_fee_bps' => 2000, 'currency' => 'USD', 'active' => true];
    }

    public function annual(): static
    {
        return $this->state(['name' => 'Annual', 'interval' => PlanInterval::Annual, 'term_days' => 365, 'price_minor' => 100000]);
    }
}
