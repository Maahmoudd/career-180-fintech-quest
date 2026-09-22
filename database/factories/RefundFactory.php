<?php

namespace Database\Factories;

use App\Models\Refund;
use App\Models\SubscriptionPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Refund> */
class RefundFactory extends Factory
{
    protected $model = Refund::class;

    public function definition(): array
    {
        return ['subscription_payment_id' => SubscriptionPayment::factory(), 'idempotency_key' => 'refund:'.$this->faker->unique()->uuid(), 'amount_minor' => 100, 'currency' => 'USD', 'status' => 'succeeded', 'reason' => 'customer_request', 'refunded_at' => now()];
    }
}
