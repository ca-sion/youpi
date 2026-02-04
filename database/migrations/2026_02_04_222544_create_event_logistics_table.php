<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_logistics', function (Blueprint $table) {
            $table->id();
            $table->string('event_name');
            $table->string('slug')->unique();
            $table->text('athletes_inscriptions_raw')->nullable();
            $table->json('inscriptions_data')->nullable();
            $table->json('raw_schedule')->nullable();
            $table->json('participants_data')->nullable();
            $table->json('transport_plan')->nullable();
            $table->json('stay_plan')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_logistics');
    }
};
