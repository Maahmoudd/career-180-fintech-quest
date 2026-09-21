<?php

namespace Database\Seeders;

use App\Domain\Actions\AllocatePaymentAction;
use App\Models\InstructorProfile;
use App\Models\StudentProfile;
use App\Models\Subscription;
use App\Models\SubscriptionInstructor;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $plan = SubscriptionPlan::factory()->create();
        $student = StudentProfile::factory()->create();
        $instructors = InstructorProfile::factory()->count(2)->create();
        $subscription = Subscription::factory()->for($student, 'student')->for($plan, 'plan')->create(['price_minor' => $plan->price_minor, 'platform_fee_bps' => $plan->platform_fee_bps, 'currency' => $plan->currency]);
        foreach ($instructors as $instructor) {
            SubscriptionInstructor::create(['subscription_id' => $subscription->id, 'instructor_profile_id' => $instructor->id, 'weight' => 1, 'effective_from' => $subscription->starts_on]);
        } $payment = SubscriptionPayment::factory()->for($subscription)->create(['amount_minor' => $plan->price_minor, 'currency' => $plan->currency]);
        app(AllocatePaymentAction::class)->handle($payment);
    }
}
