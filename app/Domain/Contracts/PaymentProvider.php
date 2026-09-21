<?php

namespace App\Domain\Contracts;

use App\Domain\Providers\ProviderResult;

interface PaymentProvider
{
    public function sendPayout(string $destination, int $amountMinor, string $currency, string $idempotencyKey): ProviderResult;

    public function payoutStatus(?string $providerReference, string $idempotencyKey): ProviderResult;

    public function refund(string $providerReference, int $amountMinor, string $currency, string $idempotencyKey): ProviderResult;
}
