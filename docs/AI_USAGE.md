# AI usage and engineering ownership

AI assistance was used as an engineering aid during this challenge. It helped with brainstorming domain boundaries, researching Laravel 13/Filament 5/Livewire 4 APIs, drafting migrations and test matrices, finding edge cases, reviewing queue and locking behavior, and improving documentation structure.

The engineering workflow was iterative: the repository and PDF were inspected first, then an architecture was written and approved. Generated drafts were reviewed against the financial invariants and Laravel project rules. A first implementation was deliberately challenged with clean migrations, seeders, static checks, and failure-oriented Pest tests.

The important decisions were made and validated against the business problem: integer minor units; basis-point fees; deterministic largest-remainder allocation; daily service-term recognition; immutable ledger/allocation history; payout reservation and active-instructor uniqueness; a persistent provider idempotency key; an external mock-provider store; unknown-outcome reconciliation; and recovery debt after a post-payout refund.

Unsafe or incomplete approaches were rejected during review. In particular, a status-only payout check, a fresh provider key on every retry, an in-memory mock idempotency map, floating-point calculations, immediate recognition of a full annual payment, and destructive ledger updates were not accepted. The implementation was changed to persist provider state, retain a stable key, use checked integer arithmetic, and test crashes/timeouts/retries.

The final implementation was manually reviewed through the relevant code paths, clean migrations and seeders were run, and Pest tests were written to assert observable financial behavior rather than implementation text. Remaining limitations are documented in `README.md` and `ARCHITECTURE.md`; no AI interaction or decision is represented here that did not occur.
