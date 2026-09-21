<?php

namespace Database\Factories;

use App\Models\IdempotencyRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

class IdempotencyRecordFactory extends Factory
{
    protected $model = IdempotencyRecord::class;

    public function definition(): array
    {
        return ['scope' => 'test', 'key' => $this->faker->unique()->uuid(), 'request_hash' => hash('sha256', $this->faker->uuid()), 'status' => 'succeeded', 'result_type' => null, 'result_id' => null, 'response' => []];
    }
}
