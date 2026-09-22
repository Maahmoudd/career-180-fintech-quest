<?php

namespace Database\Factories;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AuditLog> */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return ['actor_type' => null, 'actor_id' => null, 'event' => 'test.event', 'auditable_type' => null, 'auditable_id' => null, 'before' => null, 'after' => [], 'correlation_id' => $this->faker->uuid(), 'created_at' => now()];
    }
}
