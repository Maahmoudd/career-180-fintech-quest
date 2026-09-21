<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table): void {
            $table->date('service_starts_on')->nullable();
            $table->date('service_ends_on')->nullable();
            $table->unsignedInteger('platform_fee_bps')->nullable();
            $table->timestamp('allocated_at')->nullable()->index();
            $table->date('recognized_through')->nullable();
        });
        Schema::table('payouts', function (Blueprint $table): void {
            $table->string('destination')->nullable();
            $table->unsignedBigInteger('active_instructor_id')->nullable()->unique();
            $table->unsignedBigInteger('ledger_cutoff_id')->nullable();
            $table->timestamp('next_attempt_at')->nullable()->index();
        });
        Schema::table('payout_attempts', function (Blueprint $table): void {
            $table->string('operation')->default('submit');
        });
        Schema::table('earning_ledger_entries', function (Blueprint $table): void {
            $table->foreign('refund_id')->references('id')->on('refunds')->restrictOnDelete();
            $table->foreign('payout_id')->references('id')->on('payouts')->restrictOnDelete();
            $table->index(['instructor_profile_id', 'currency', 'id'], 'ledger_balance_index');
        });
        Schema::create('plan_changes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->restrictOnDelete();
            $table->foreignId('old_plan_id')->constrained('subscription_plans')->restrictOnDelete();
            $table->foreignId('new_plan_id')->constrained('subscription_plans')->restrictOnDelete();
            $table->string('idempotency_key')->unique();
            $table->string('request_hash', 64);
            $table->date('effective_on');
            $table->bigInteger('adjustment_minor');
            $table->unsignedBigInteger('credit_minor');
            $table->unsignedBigInteger('replacement_minor');
            $table->json('metadata');
            $table->timestamps();
        });
        if (DB::getDriverName() === 'mysql') {
            foreach ([
                'subscription_plans' => ['platform_fee_bps <= 10000', 'price_minor > 0', 'term_days > 0'],
                'subscriptions' => ['ends_on > starts_on', 'platform_fee_bps <= 10000'],
                'subscription_instructors' => ['weight > 0 AND weight <= 1000000'],
                'subscription_payments' => ['amount_minor > 0 AND amount_minor <= 1000000000000'],
                'payouts' => ['amount_minor > 0', "status IN ('pending','reserved','submitting','unknown','reconciling','succeeded','failed_retryable','failed_permanent')"],
                'refunds' => ['amount_minor > 0'],
            ] as $table => $checks) {
                foreach ($checks as $i => $check) {
                    DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_check_{$i} CHECK ({$check})");
                }
            }
        }
        foreach (['earning_ledger_entries', 'revenue_allocations', 'refund_allocations', 'audit_logs', 'payout_attempts'] as $table) {
            foreach (['UPDATE', 'DELETE'] as $operation) {
                $name = $table.'_no_'.strtolower($operation);
                if (DB::getDriverName() === 'mysql') {
                    DB::unprepared("CREATE TRIGGER {$name} BEFORE {$operation} ON {$table} FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Financial history is immutable'");
                } else {
                    DB::unprepared("CREATE TRIGGER {$name} BEFORE {$operation} ON {$table} BEGIN SELECT RAISE(ABORT, 'Financial history is immutable'); END");
                }
            }
        }
    }

    public function down(): void
    {
        foreach (['earning_ledger_entries', 'revenue_allocations', 'refund_allocations', 'audit_logs', 'payout_attempts'] as $table) {
            foreach (['update', 'delete'] as $operation) {
                DB::unprepared("DROP TRIGGER IF EXISTS {$table}_no_{$operation}");
            }
        }
        if (DB::getDriverName() === 'mysql') {
            foreach (['subscription_plans' => 3, 'subscriptions' => 2, 'subscription_instructors' => 1, 'subscription_payments' => 1, 'payouts' => 2, 'refunds' => 1] as $table => $count) {
                for ($i = 0; $i < $count; $i++) {
                    DB::statement("ALTER TABLE {$table} DROP CHECK {$table}_check_{$i}");
                }
            }
        }
        Schema::dropIfExists('plan_changes');
        Schema::table('earning_ledger_entries', function (Blueprint $table): void {
            $table->dropForeign(['refund_id']);
            $table->dropForeign(['payout_id']);
            $table->dropIndex('ledger_balance_index');
        });
        Schema::table('payout_attempts', fn (Blueprint $table) => $table->dropColumn('operation'));
        Schema::table('payouts', function (Blueprint $table): void {
            $table->dropUnique(['active_instructor_id']);
            $table->dropColumn(['destination', 'active_instructor_id', 'ledger_cutoff_id', 'next_attempt_at']);
        });
        Schema::table('subscription_payments', fn (Blueprint $table) => $table->dropColumn(['service_starts_on', 'service_ends_on', 'platform_fee_bps', 'allocated_at', 'recognized_through']));
    }
};
