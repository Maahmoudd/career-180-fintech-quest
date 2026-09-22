<?php

namespace Database\Factories;

use App\Models\ProviderEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProviderEvent> */
class ProviderEventFactory extends Factory
{
    protected $model = ProviderEvent::class;

    public function definition(): array
    {
        return ['provider' => 'mock', 'provider_reference' => 'mock_'.$this->faker->uuid(), 'event_type' => 'payout.updated', 'outcome' => 'succeeded', 'payload' => [], 'occurred_at' => now()];
    }
}
