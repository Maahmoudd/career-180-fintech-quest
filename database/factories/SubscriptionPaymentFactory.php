<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionPaymentFactory extends Factory
{
    protected $model = SubscriptionPayment::class;

    public function definition(): array
    {
        return ['subscription_id' => Subscription::factory(), 'idempotency_key' => 'payment:'.$this->faker->unique()->uuid(), 'external_reference' => 'pay_'.$this->faker->unique()->uuid(), 'amount_minor' => 10000, 'currency' => 'USD', 'status' => PaymentStatus::Succeeded, 'paid_at' => now(), 'metadata' => []];
    }
}
