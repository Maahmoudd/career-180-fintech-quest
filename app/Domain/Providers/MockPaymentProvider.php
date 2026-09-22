<?php

namespace App\Domain\Providers;

use App\Domain\Contracts\PaymentProvider;
use App\Enums\ProviderOutcome;
use App\Support\Money;
use InvalidArgumentException;
use PDO;
use Throwable;

/** Durable external-system simulator. Its SQLite transaction cannot be rolled back by the application DB. */
final class MockPaymentProvider implements PaymentProvider
{
    private PDO $store;

    private string $mode;

    public function __construct(?string $mode = null, ?string $path = null)
    {
        $this->mode = $mode ?? (string) config('services.mock_payments.mode', 'random');
        $this->store = new PDO('sqlite:'.($path ?? config('services.mock_payments.path')), null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $this->store->exec('PRAGMA busy_timeout=10000');
        $this->store->exec('CREATE TABLE IF NOT EXISTS transfers (idempotency_key TEXT PRIMARY KEY, request_hash TEXT NOT NULL, reference TEXT NOT NULL UNIQUE, outcome TEXT NOT NULL, amount_minor INTEGER NOT NULL, currency TEXT NOT NULL, status_checks INTEGER NOT NULL DEFAULT 0, hidden_checks INTEGER NOT NULL DEFAULT 0)');
    }

    public function sendPayout(string $destination, int $amountMinor, string $currency, string $idempotencyKey): ProviderResult
    {
        return $this->transfer('payout', $destination, $amountMinor, $currency, $idempotencyKey);
    }

    public function refund(string $providerReference, int $amountMinor, string $currency, string $idempotencyKey): ProviderResult
    {
        return $this->transfer('refund', $providerReference, $amountMinor, $currency, $idempotencyKey);
    }

    private function transfer(string $operation, string $destination, int $amountMinor, string $currency, string $key): ProviderResult
    {
        new Money($amountMinor, $currency);
        if ($amountMinor === 0 || $destination === '' || $key === '') {
            throw new InvalidArgumentException('A positive transfer, destination and key are required.');
        }
        $hash = hash('sha256', json_encode([$operation, $destination, $amountMinor, $currency], JSON_THROW_ON_ERROR));
        $this->store->exec('BEGIN IMMEDIATE');
        try {
            $existing = $this->find($key);
            if ($existing !== null) {
                if ($existing['request_hash'] !== $hash) {
                    throw new InvalidArgumentException('Provider idempotency payload conflict.');
                }
                $result = $this->result($existing);
            } else {
                $mode = $this->mode === 'random' ? ['success', 'permanent_failure', 'timeout_after_success'][random_int(0, 2)] : $this->mode;
                if ($mode === 'timeout_before_processing' || $mode === 'temporary_failure') {
                    $result = new ProviderResult($mode === 'temporary_failure' ? ProviderOutcome::FailedTemporary : ProviderOutcome::Unknown, null, 'No response confirming acceptance.');
                } else {
                    if (! in_array($mode, ['success', 'permanent_failure', 'timeout_after_success', 'delayed_confirmation'], true)) {
                        throw new InvalidArgumentException('Unknown mock provider mode.');
                    }
                    $reference = 'mock_'.hash('sha256', $key);
                    $outcome = $mode === 'permanent_failure' ? 'failed_permanent' : 'succeeded';
                    $statement = $this->store->prepare('INSERT INTO transfers (idempotency_key,request_hash,reference,outcome,amount_minor,currency,hidden_checks) VALUES (?,?,?,?,?,?,?)');
                    $statement->execute([$key, $hash, $reference, $outcome, $amountMinor, $currency, $mode === 'delayed_confirmation' ? 2 : 0]);
                    $result = in_array($mode, ['timeout_after_success', 'delayed_confirmation'], true)
                        ? new ProviderResult(ProviderOutcome::Unknown, null, 'Response lost after acceptance.')
                        : new ProviderResult(ProviderOutcome::from($outcome), $reference, 'Provider terminal result.');
                }
            }
            $this->store->exec('COMMIT');

            return $result;
        } catch (Throwable $exception) {
            $this->store->exec('ROLLBACK');
            throw $exception;
        }
    }

    public function payoutStatus(?string $providerReference, string $idempotencyKey): ProviderResult
    {
        $record = $this->find($idempotencyKey);
        if ($record === null) {
            return new ProviderResult(ProviderOutcome::FailedTemporary, null, 'Authoritative lookup: key not accepted.');
        }
        if ($providerReference !== null && $record['reference'] !== $providerReference) {
            throw new InvalidArgumentException('Provider reference does not match key.');
        }
        $statement = $this->store->prepare('UPDATE transfers SET status_checks = status_checks + 1 WHERE idempotency_key = ?');
        $statement->execute([$idempotencyKey]);
        if ((int) $record['status_checks'] < (int) $record['hidden_checks']) {
            return new ProviderResult(ProviderOutcome::Unknown, null, 'Confirmation is still unavailable.');
        }

        return $this->result($record);
    }

    /** @return array<string, mixed>|null */
    private function find(string $key): ?array
    {
        $statement = $this->store->prepare('SELECT * FROM transfers WHERE idempotency_key = ?');
        $statement->execute([$key]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /** @param array<string, mixed> $record */
    private function result(array $record): ProviderResult
    {
        return new ProviderResult(ProviderOutcome::from($record['outcome']), $record['reference'], 'Stored provider result.', ['amount_minor' => (int) $record['amount_minor'], 'currency' => $record['currency']]);
    }

    public function successfulTransfers(): int
    {
        $statement = $this->store->query("SELECT COUNT(*) FROM transfers WHERE outcome = 'succeeded'");
        if ($statement === false) {
            throw new \RuntimeException('Unable to query the mock provider store.');
        }

        return (int) $statement->fetchColumn();
    }
}
