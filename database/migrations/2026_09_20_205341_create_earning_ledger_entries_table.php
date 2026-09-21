<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('earning_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_profile_id')->constrained()->restrictOnDelete();
            $table->foreignId('subscription_payment_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('revenue_allocation_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('refund_id')->nullable();
            $table->foreignId('payout_id')->nullable();
            $table->string('type');
            $table->bigInteger('amount_minor');
            $table->string('currency', 3);
            $table->string('source_key')->unique();
            $table->timestamp('effective_at');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['instructor_profile_id', 'type', 'effective_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('earning_ledger_entries');
    }
};
