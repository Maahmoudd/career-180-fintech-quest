<?php

namespace App\Domain\Services;

use App\Support\IntegerMath;
use Brick\Math\BigInteger;
use InvalidArgumentException;

final class RevenueAllocationService
{
    /**
     * @param  list<array{instructor_id:int, weight:int}>  $participants
     * @return list<array{instructor_id:int, amount:int, weight:int, residual_rank:int}>
     */
    public function allocate(int $distributableMinor, array $participants): array
    {
        if ($distributableMinor < 0 || $distributableMinor > IntegerMath::MAX_AMOUNT || $participants === []) {
            throw new InvalidArgumentException('Amount and participants must be valid.');
        }
        $totalWeight = 0;
        $ids = [];
        foreach ($participants as $participant) {
            if ($participant['weight'] <= 0 || isset($ids[$participant['instructor_id']])) {
                throw new InvalidArgumentException('Positive weights and distinct recipients required.');
            }
            $ids[$participant['instructor_id']] = true;
            $totalWeight += $participant['weight'];
            if ($totalWeight > 1_000_000_000_000) {
                throw new InvalidArgumentException('Weight total too large.');
            }
        }
        $rows = [];
        $allocated = 0;
        foreach ($participants as $participant) {
            // Quotient/remainder decomposition avoids amount * weight overflowing on refunds.
            $quotient = intdiv($distributableMinor, $totalWeight);
            $remainderProduct = BigInteger::of($distributableMinor % $totalWeight)->multipliedBy($participant['weight']);
            [$partial, $remainder] = $remainderProduct->quotientAndRemainder($totalWeight);
            $floor = IntegerMath::multiply($quotient, $participant['weight']) + $partial->toInt();
            $rows[] = ['instructor_id' => $participant['instructor_id'], 'amount' => $floor, 'weight' => $participant['weight'], 'remainder' => $remainder->toInt()];
            $allocated += $floor;
        }
        usort($rows, fn (array $a, array $b): int => ($b['remainder'] <=> $a['remainder']) ?: ($a['instructor_id'] <=> $b['instructor_id']));
        $residual = $distributableMinor - $allocated;
        foreach ($rows as $index => &$row) {
            $row['amount'] += $index < $residual ? 1 : 0;
            $row['residual_rank'] = $index + 1;
            unset($row['remainder']);
        }
        unset($row);

        return $rows;
    }
}
