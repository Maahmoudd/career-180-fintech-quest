<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenue_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_payment_id')->constrained()->restrictOnDelete();
            $table->foreignId('instructor_profile_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('recipient_type');
            $table->unsignedBigInteger('amount_minor');
            $table->unsignedInteger('weight')->nullable();
            $table->unsignedBigInteger('residual_rank')->nullable();
            $table->string('currency', 3);
            $table->string('allocation_key')->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['instructor_profile_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenue_allocations');
    }
};
