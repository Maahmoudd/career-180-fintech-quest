<?php

namespace App\Support;

final readonly class LedgerBalance
{
    public function __construct(public int $earned, public int $paid, public int $debited, public int $reserved = 0) {}

    public function outstanding(): int
    {
        return $this->earned - $this->paid - $this->debited;
    }

    public function available(): int
    {
        return max(0, $this->outstanding() - $this->reserved);
    }
}
