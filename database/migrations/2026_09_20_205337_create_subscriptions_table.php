<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained()->restrictOnDelete();
            $table->string('status');
            $table->string('currency', 3);
            $table->unsignedBigInteger('price_minor');
            $table->unsignedInteger('platform_fee_bps');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'ends_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
