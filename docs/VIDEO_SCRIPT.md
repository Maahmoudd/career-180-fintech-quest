# Required video recording plan (15-20 minutes)

This file is the recording checklist. Record the session after the complete test suite passes and replace the video-link placeholder in `docs/evidence/VIDEO_LINK.txt` with the uploaded URL.

## 1. Introduction (2-3 minutes)

- Introduce yourself and state that the project is a subscription instructor revenue ledger.
- Explain any prior payments, subscription, ledger, or high-volume work truthfully.
- State the key approach: integer ledger, daily recognition, durable payout intent, stable provider idempotency, and reconciliation.

## 2. Architecture walkthrough (5 minutes)

- Show the model relationship diagram from `docs/ARCHITECTURE.md`.
- Show the migrations for subscriptions, payments, allocations, ledger entries, refunds, payouts, attempts, provider events, and audit logs.
- Explain minor units, basis points, largest-remainder rounding, and the exact gross conservation invariant.
- Explain daily service-day recognition and why annual upfront money is not immediately payable.
- Explain payout reservation, row locks, unique active-instructor reservation, provider key, and the unknown state.
- State the trade-off between append-only auditability and query/projection cost.

## 3. Failure demonstration (5-7 minutes)

Run each scenario with a clean database or an isolated provider-store file:

1. Run `payouts:process` twice and show one logical payout and one provider transfer.
2. Execute the same `ProcessPayoutJob` twice and show no duplicate payout ledger entry.
3. Use `temporary_failure`, then retry with `success` and show the original idempotency key.
4. Use `timeout_before_processing`, retry, and show that the same key creates one transfer.
5. Use `timeout_after_success`, show local `unknown`, run reconciliation, and show provider success without a second transfer.
6. Use `delayed_confirmation`, show repeated unknown status, then final success.
7. Process a refund after allocation and after payout; show the instructor recoverable negative balance.
8. Run a 1-minor-unit-at-a-time refund sequence and show that no cents are lost.

## 4. Testing strategy (2-3 minutes)

- Run `XDEBUG_MODE=off php artisan test --compact`.
- Show the passing output and explain why each failure test exists.
- Point to unit tests for arithmetic and feature tests for database state, provider outcomes, retry safety, refunds, and immutable history.

## 5. AI usage and engineering decisions (2-3 minutes)

- Explain that AI supported research, drafts, edge-case discovery, review, and documentation.
- Explain the engineering decisions you personally reviewed.
- Explain why status-only checks, new retry keys, floating point, and destructive balance updates were rejected.
- Show `docs/AI_USAGE.md`.

## 6. Future improvements (1-2 minutes)

- Add real provider adapters and signed webhooks.
- Add MySQL concurrency CI and load tests at stated scale.
- Add paginated ledger/reconciliation resources, manual approval, and operational alerting.
- Add an accounting close process and formally versioned tax/currency rules.
