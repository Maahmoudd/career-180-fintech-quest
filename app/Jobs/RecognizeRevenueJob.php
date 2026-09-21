<?php

namespace App\Jobs;

use App\Domain\Actions\AllocatePaymentAction;
use App\Domain\Actions\RecognizeRevenueAction;
use App\Models\SubscriptionPayment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class RecognizeRevenueJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(public int $paymentId) {}

    public function handle(AllocatePaymentAction $allocate, RecognizeRevenueAction $recognize): void
    {
        $payment = SubscriptionPayment::findOrFail($this->paymentId);
        $allocate->handle($payment);
        $recognize->handle($payment);
    }
}
