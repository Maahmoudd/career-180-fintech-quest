<?php

namespace App\Support;

use InvalidArgumentException;

final readonly class Money
{
    public function __construct(public int $minor, public string $currency = 'USD')
    {
        if ($minor < 0 || $minor > IntegerMath::MAX_AMOUNT) {
            throw new InvalidArgumentException('Money is outside the supported minor-unit range.');
        }
        if (! in_array($currency, ['USD', 'EGP', 'EUR', 'GBP'], true)) {
            throw new InvalidArgumentException('Unsupported currency. Only configured two-decimal currencies are accepted.');
        }
    }

    public function add(self $other): self
    {
        $this->assertCurrency($other);

        return new self($this->minor + $other->minor, $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->assertCurrency($other);

        return new self($this->minor - $other->minor, $this->currency);
    }

    public function percentage(int $basisPoints): self
    {
        if ($basisPoints < 0 || $basisPoints > 10000) {
            throw new InvalidArgumentException('Basis points must be between 0 and 10000.');
        }

        return new self(IntegerMath::proportion($this->minor, $basisPoints, 10000), $this->currency);
    }

    private function assertCurrency(self $other): void
    {
        if ($other->currency !== $this->currency) {
            throw new InvalidArgumentException('Currency mismatch.');
        }
    }
}
