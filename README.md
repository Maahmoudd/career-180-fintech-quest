# Instructor Revenue Ledger

This project implements the money core for a subscription learning platform. It accepts upfront subscription payments, snapshots the platform fee and instructor participation, recognizes instructor revenue over the service term, records refunds as append-only reversals, and submits each instructor's payable balance through an idempotent provider boundary.

The application uses the current compatible stack in this repository: PHP 8.4, Laravel 13, Livewire 4, Filament 5, Pest 5, MySQL 8.4, Redis 7, Vite 8, and Tailwind 4. The challenge named older versions; the environment was intentionally updated to the latest compatible releases as directed.

## Requirements

- PHP 8.4 with PDO SQLite for local tests, or Docker.
- Composer 2.9+.
- Node 22+ and npm for frontend assets, or Docker for the PHP/MySQL/Redis runtime.
- Docker 29+ and Docker Compose v2 for the reproducible environment.

## Docker setup

```bash
cp .env.example .env
# Set APP_KEY after the containers are available.
docker compose build
docker compose up -d mysql redis
docker compose run --rm app php artisan key:generate
docker compose up -d app worker scheduler
docker compose exec app php artisan db:seed --force
```

The web application is available at `http://localhost:8000`. Filament is at `/admin`; create an administrator in a controlled environment with `php artisan tinker` or add one to a private seed override. The compose file runs MySQL 8.4, Redis 7, an application server, a queue worker, and a scheduler. Do not commit `.env` or production secrets.

## Local installation

```bash
composer install
cp .env.example .env
php artisan key:generate
# For local SQLite, set DB_CONNECTION=sqlite and DB_DATABASE=database/database.sqlite.
touch database/database.sqlite
php artisan migrate:fresh --seed
npm install
npm run build
```

Set `MOCK_PAYMENT_MODE` to one of `success`, `permanent_failure`, `temporary_failure`, `timeout_before_processing`, `timeout_after_success`, `delayed_confirmation`, or `random`. The mock provider keeps its authoritative transfer store at `MOCK_PROVIDER_PATH`, separate from the Laravel database, so provider success can survive an application crash.

## Main workflows

1. Create a plan, student, instructors, subscription, and successful `SubscriptionPayment`.
2. Run `AllocatePaymentAction`. It snapshots the platform fee and weighted instructor allocation exactly once.
3. Run `RecognizeRevenueAction` from the daily scheduler/queue. Recognition uses integer day proportions and never recognizes future service.
4. Run `php artisan payouts:process` to dispatch one job per active instructor. Each logical payout has one durable idempotency key and one active reservation.
5. Run `php artisan payouts:reconcile` to revisit unknown provider outcomes using the same provider key.
6. Run `ProcessRefundAction` with a unique refund key. It allocates the refund proportionally, reverses only recognized earnings, and leaves post-payout debt visible.
7. Use `ChangeSubscriptionPlanAction` for an idempotent mid-term plan change. The action records a `PlanChange` snapshot and a deterministic remaining-term adjustment.

## Useful commands

```bash
php artisan migrate:fresh --seed
php artisan payouts:process
php artisan payouts:process --instructor=1
php artisan payouts:reconcile
php artisan queue:work database --tries=5 --timeout=60
php artisan schedule:work
php artisan test --compact
vendor/bin/pint --dirty --format agent
vendor/bin/phpstan analyse
composer audit
```

## Tests

The Pest suite covers integer money, largest-remainder allocation, payment idempotency, daily recognition, rollback, immutable history, payout duplicates and retries, provider timeout-before/after-processing, delayed confirmation, permanent errors, provider key conflicts, partial and repeated refunds, post-payout recoverable balances, and state transition protection.

```bash
XDEBUG_MODE=off php artisan test --compact
```

The screenshot in `docs/evidence/passing-tests.txt` is the captured passing test output. A video walkthrough script and required scenario checklist are in `docs/VIDEO_SCRIPT.md`; the submission link must be added by the candidate after recording and uploading the video.

## Access and authorization

Filament panel access is limited to `admin` and `instructor` roles. Administrators see all balances. Instructors are scoped to their own profile. Financial writes are application actions and policies, not direct UI mutations. The balance page is read-only.

## Important assumptions

- All supported currencies use two decimal minor units in this challenge and are stored as integer minor units.
- Platform fees use basis points and round down; the remainder belongs to instructors and is distributed by deterministic largest remainder.
- Instructor participation weights are snapshotted at payment time.
- Revenue is earned daily over the service term, not all at the upfront payment date.
- Refunds reverse deferred/unearned value first; recognized value becomes a negative ledger entry. Funds paid before a refund become recoverable debt.
- A provider timeout is an unknown outcome. It is reconciled by key/reference instead of issuing a new transfer.
- `PlanChange` records commercial adjustment arithmetic. A production billing adapter would connect the adjustment to a customer charge/refund provider operation.

## Known limitations

- The mock provider is intentionally local and does not model provider webhooks or signature verification.
- The current Filament page is a read-only operational view; a production product would add paginated ledger resources and explicit reconciliation approval controls.
- MySQL is the deployment database. SQLite is supported for the fast test suite; MySQL constraint behavior should be exercised in CI before production release.
- No real video can be uploaded from this repository. `docs/VIDEO_SCRIPT.md` is the prepared recording plan and must be paired with the candidate's actual recording link.
