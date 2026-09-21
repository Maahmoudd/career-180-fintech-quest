<?php

namespace App\Console\Commands;

use App\Jobs\ProcessPayoutJob;
use App\Models\InstructorProfile;
use Illuminate\Console\Command;

class ProcessPayouts extends Command
{
    protected $signature = 'payouts:process {--instructor=}';

    protected $description = 'Queue safe instructor payouts.';

    public function handle(): int
    {
        $q = InstructorProfile::query()->where('active', true);
        if ($this->option('instructor')) {
            $q->whereKey($this->option('instructor'));
        } $count = 0;
        $q->pluck('id')->each(fn ($id) => [ProcessPayoutJob::dispatch((int) $id), $count++]);
        $this->info("Queued {$count} payout jobs.");

        return self::SUCCESS;
    }
}
