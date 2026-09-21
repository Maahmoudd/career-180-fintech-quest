<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payout_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('attempt_number');
            $table->string('outcome');
            $table->string('provider_reference')->nullable();
            $table->string('idempotency_key');
            $table->text('message')->nullable();
            $table->json('response')->nullable();
            $table->timestamp('attempted_at');
            $table->timestamps();
            $table->unique(['payout_id', 'attempt_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_attempts');
    }
};
