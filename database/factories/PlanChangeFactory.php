<?php

namespace Database\Factories;

use App\Models\PlanChange;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PlanChange> */
class PlanChangeFactory extends Factory
{
    protected $model = PlanChange::class;

    public function definition(): array
    {
        return ['subscription_id' => Subscription::factory(), 'old_plan_id' => SubscriptionPlan::factory(), 'new_plan_id' => SubscriptionPlan::factory(), 'idempotency_key' => 'plan-change:'.$this->faker->unique()->uuid(), 'request_hash' => hash('sha256', $this->faker->uuid()), 'effective_on' => now()->addDay()->toDateString(), 'adjustment_minor' => 100, 'credit_minor' => 0, 'replacement_minor' => 100, 'metadata' => []];
    }
}
