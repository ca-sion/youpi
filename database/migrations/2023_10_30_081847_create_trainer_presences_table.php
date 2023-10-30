<?php

use App\Models\Event;
use App\Models\Trainer;
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
        Schema::create('trainer_presences', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Event::class)->constrained()->nullOnUpdate()->cascadeOnDelete();
            $table->foreignIdFor(Trainer::class)->constrained()->nullOnUpdate()->cascadeOnDelete();
            $table->boolean('presence')->nullable();
            $table->string('note')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainer_presences');
    }
};
