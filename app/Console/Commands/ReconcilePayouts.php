<?php

namespace App\Console\Commands;

use App\Jobs\ReconcilePayoutJob;
use App\Models\Payout;
use Illuminate\Console\Command;

class ReconcilePayouts extends Command
{
    protected $signature = 'payouts:reconcile';

    protected $description = 'Reconcile provider-unknown payouts.';

    public function handle(): int
    {
        $count = 0;
        Payout::query()->whereIn('status', ['unknown', 'reconciling'])->whereNotNull('provider_reference')->pluck('id')->each(fn ($id) => [ReconcilePayoutJob::dispatch((int) $id), $count++]);
        $this->info("Queued {$count} reconciliation jobs.");

        return self::SUCCESS;
    }
}
