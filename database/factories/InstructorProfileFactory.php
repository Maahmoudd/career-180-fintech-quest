<?php

namespace Database\Factories;

use App\Models\InstructorProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstructorProfileFactory extends Factory
{
    protected $model = InstructorProfile::class;

    public function definition(): array
    {
        $user = User::factory()->state(['role' => 'instructor']);

        return ['user_id' => $user, 'payout_account' => 'acct_'.$this->faker->unique()->numerify('######'), 'currency' => 'USD', 'active' => true];
    }
}
