<?php

namespace App\Jobs;

use App\Domain\Actions\ProcessPayoutAction;
use App\Models\InstructorProfile;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class ProcessPayoutJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $timeout = 60;

    /** @var list<int> */
    public array $backoff = [10, 30, 120, 300];

    public function __construct(public int $instructorId) {}

    public function handle(ProcessPayoutAction $action): void
    {
        $action->handle(InstructorProfile::findOrFail($this->instructorId));
    }
}
