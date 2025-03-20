<?php

use App\Models\Trainer;
use App\Models\AthleteGroup;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('athlete_group_trainer', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Trainer::class)->constrained()->nullOnUpdate()->cascadeOnDelete();
            $table->foreignIdFor(AthleteGroup::class)->constrained()->nullOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('athlete_group_trainer');
    }
};
