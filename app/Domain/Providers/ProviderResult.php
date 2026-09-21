<?php

namespace App\Domain\Providers;

use App\Enums\ProviderOutcome;

final readonly class ProviderResult
{
    /** @param array<string, mixed> $payload */
    public function __construct(public ProviderOutcome $outcome, public ?string $providerReference = null, public ?string $message = null, public array $payload = []) {}
}
