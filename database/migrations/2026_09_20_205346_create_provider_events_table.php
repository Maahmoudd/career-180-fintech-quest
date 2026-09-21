<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('provider_reference');
            $table->string('event_type');
            $table->string('outcome');
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->unique(['provider', 'provider_reference', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_events');
    }
};
