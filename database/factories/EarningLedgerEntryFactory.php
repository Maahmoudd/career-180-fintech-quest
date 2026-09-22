<?php

namespace Database\Factories;

use App\Enums\LedgerEntryType;
use App\Models\EarningLedgerEntry;
use App\Models\InstructorProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<EarningLedgerEntry> */
class EarningLedgerEntryFactory extends Factory
{
    protected $model = EarningLedgerEntry::class;

    public function definition(): array
    {
        return ['instructor_profile_id' => InstructorProfile::factory(), 'subscription_payment_id' => null, 'revenue_allocation_id' => null, 'refund_id' => null, 'payout_id' => null, 'type' => LedgerEntryType::Earned, 'amount_minor' => 1000, 'currency' => 'USD', 'source_key' => 'entry:'.$this->faker->unique()->uuid, 'effective_at' => now(), 'metadata' => []];
    }
}
