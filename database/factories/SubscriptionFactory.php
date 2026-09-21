<?php

namespace Database\Factories;

use App\Enums\SubscriptionStatus;
use App\Models\StudentProfile;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        $plan = SubscriptionPlan::factory();
        $start = now()->startOfDay();

        return ['student_profile_id' => StudentProfile::factory(), 'subscription_plan_id' => $plan, 'status' => SubscriptionStatus::Active, 'currency' => 'USD', 'price_minor' => 10000, 'platform_fee_bps' => 2000, 'starts_on' => $start, 'ends_on' => $start->copy()->addDays(30), 'cancelled_at' => null];
    }
}
