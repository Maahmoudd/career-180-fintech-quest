<?php

namespace App\Domain\Services;

use App\Models\EarningLedgerEntry;
use App\Models\InstructorProfile;
use App\Support\LedgerBalance;

final class BalanceService
{
    public function forInstructor(InstructorProfile $instructor): LedgerBalance
    {
        $totals = EarningLedgerEntry::query()->where('instructor_profile_id', $instructor->id)->where('currency', $instructor->currency)
            ->selectRaw("COALESCE(SUM(CASE WHEN type IN ('earned','adjustment') THEN amount_minor ELSE 0 END),0) AS earned, COALESCE(SUM(CASE WHEN type = 'payout' THEN -amount_minor ELSE 0 END),0) AS paid, COALESCE(SUM(CASE WHEN type IN ('refund','clawback') THEN -amount_minor ELSE 0 END),0) AS debited")->toBase()->first();
        $reserved = (int) $instructor->payouts()->whereNotNull('active_instructor_id')->where('status', '!=', 'failed_permanent')->sum('amount_minor');

        return new LedgerBalance((int) $totals->earned, (int) $totals->paid, (int) $totals->debited, $reserved);
    }

    public function outstanding(InstructorProfile $instructor): int
    {
        return $this->forInstructor($instructor)->available();
    }
}
