# Architecture

## Scope

The bounded context is instructor revenue accounting. Subscription payments are immutable commercial events; revenue allocations snapshot who is entitled to the payment; the earning ledger is the source of truth for instructor balances; payouts are durable external side effects with a state machine and provider reconciliation.

## Domain model

`User` owns either an `InstructorProfile` or `StudentProfile`. `SubscriptionPlan` contains a price, term days, currency, and platform fee basis points. `Subscription` snapshots those commercial values. `SubscriptionInstructor` records weighted participation and effective dates. `SubscriptionPayment` records a settled upfront payment. `RevenueAllocation` splits it into platform and instructor rows. `EarningLedgerEntry` records recognized earnings, payout debits, refunds, and adjustments. `Refund` and `RefundAllocation` reverse the original event. `Payout` is one logical payment intent; `PayoutAttempt` is its append-only attempt history. `ProviderEvent`, `IdempotencyRecord`, and `AuditLog` make external state and decisions observable. `PlanChange` records the mandatory mid-term plan-change bonus.

## Money and allocation

Every amount is an integer minor unit. `Money` validates currency and range; `IntegerMath` performs checked multiplication and quotient arithmetic. No floating-point number enters a financial calculation. Fees are `floor(gross * basis_points / 10_000)`. The remaining distributable amount is allocated with largest remainder: each recipient receives a quotient floor, then residual cents go to the largest exact remainders, with instructor ID as the deterministic tie-breaker. Therefore:

```text
gross payment = platform allocation + sum(instructor allocations)
```

Weights are positive integers and each recipient appears once. Payment, allocation, refund, and payout amounts are bounded by database/application checks.

## Revenue recognition

Payments are allocated once at settlement, but instructor earnings are recognized by UTC service day. A daily job computes the cumulative target using integer proportions and appends only the delta since the prior run. No future day can be recognized. Allocation snapshots are independent of later plan edits.

## Refunds

A refund has an idempotency key and cannot exceed the unrefunded payment. Refund allocation uses the remaining capacity of every original allocation, including the platform allocation. Only recognized instructor value creates a negative instructor ledger entry; an unearned refund reduces future recognition capacity. If the instructor was already paid, the negative ledger balance is recoverable and no new payout is created.

## Payout state machine

```text
reserved -> submitting -> succeeded
reserved -> submitting -> failed_retryable -> submitting
reserved -> submitting -> unknown -> reconciling -> succeeded
reserved -> submitting -> failed_permanent
```

`active_instructor_id` has a unique index, so one instructor can have only one active logical payout. The reservation stores the ledger cutoff and amount. A provider attempt is written before the provider call, so an application crash leaves durable intent. A payout success creates exactly one negative payout ledger entry with a unique `source_key`. Failed payouts retain their logical record and retry with the same key; permanent failures require an explicit operator decision.

## Provider timeout and unknown outcomes

The `PaymentProvider` interface separates `sendPayout`, `payoutStatus`, and refunds from the domain. `MockPaymentProvider` uses a separate SQLite transfer store and a durable idempotency key. `timeout_after_success` stores a successful transfer but returns `unknown`; reconciliation queries the provider using the original key even if no reference reached the application. `timeout_before_processing` stores no transfer, so later retry with the same key is safe. A different payload with an existing key is rejected.

## Transactions and locks

Payment allocation, recognition, refunds, plan changes, and payout reservation/state transitions run in retryable database transactions. Pessimistic locks serialize a payment, subscription, instructor, or payout before financial decisions. External calls happen outside the application transaction. Queue jobs are retryable and use the same durable state/key; queue `retry_after` is longer than the 60-second worker timeout.

## Auditability and immutability

Ledger, allocation, refund-allocation, payout-attempt, and audit tables have database triggers that reject updates/deletes. Every financial action stores source references, a timestamp, a correlation/audit event, and provider response metadata. Reconciliation can explain both local and external outcomes.

## Filament, Livewire, and Alpine

Filament 5 provides `/admin` authentication and the Instructor Balances page. Administrators see all instructors; an instructor sees only their own profile. The Livewire balance dashboard supports debounced search and Alpine collapse/transition behavior. The financial page is read-only and does not bypass action/policy boundaries.

## Bonus: mid-term plan changes

`ChangeSubscriptionPlanAction` locks the subscription, validates the effective date and currency, calculates remaining old/new value with integer day proportions, writes an idempotent `PlanChange`, and updates only the future commercial snapshot. Existing payment allocations remain immutable. The recorded adjustment is the basis for a production billing-provider operation.

## Scaling

Indexes target status/date scans, provider keys, instructor ledger queries, and unique idempotency boundaries. Recognition and payout scheduling are queue-based and chunkable. The `ledger_cutoff_id` creates a stable reservation boundary. At larger volumes, balance projections can be maintained as a separately reconciled read model, ledger tables can be partitioned by period, and Redis/Horizon can provide queue balancing and metrics. The append-only source ledger remains authoritative.

## Security

Mass assignment is explicit. Filament access is role-gated; the policy scopes instructor records. Provider credentials belong in environment/secret management. The UI escapes names and balances through Blade. Sensitive actions should be authenticated, authorized, rate-limited, and logged in a production adapter.

## Trade-offs and limitations

Daily recognition is more defensible for annual upfront terms but requires scheduled processing. Integer basis points are simple and auditable but cannot represent arbitrary fractional pricing rules. A separate mock-provider store makes crash behavior demonstrable but is not a real provider. The current implementation intentionally avoids a full LMS and keeps operational UI read-only; production additions would include signed webhooks, reconciliation dashboards, manual approval, provider adapters, MySQL concurrency tests, and a formal accounting close process.
