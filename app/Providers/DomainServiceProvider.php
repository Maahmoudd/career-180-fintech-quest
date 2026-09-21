<?php

namespace App\Providers;

use App\Domain\Contracts\PaymentProvider;
use App\Domain\Providers\MockPaymentProvider;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentProvider::class, MockPaymentProvider::class);
    }
}
