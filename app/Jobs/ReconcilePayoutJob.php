<?php

namespace App\Jobs;

use App\Domain\Actions\ProcessPayoutAction;
use App\Models\Payout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class ReconcilePayoutJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $timeout = 60;

    /** @var list<int> */
    public array $backoff = [10, 30, 120, 300];

    public function __construct(public int $payoutId) {}

    public function handle(ProcessPayoutAction $action): void
    {
        $action->submit(Payout::findOrFail($this->payoutId));
    }
}
