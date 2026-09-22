<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_instructors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_profile_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('weight');
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->timestamps();
            $table->unique(['subscription_id', 'instructor_profile_id', 'effective_from'], 'subscription_instructors_participation_unique');
            $table->index(['subscription_id', 'effective_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_instructors');
    }
};
