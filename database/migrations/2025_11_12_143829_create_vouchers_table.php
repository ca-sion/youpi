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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->uuid('code_unique')->unique();
            $table->string('type')->nullable();
            
            $table->string('athlete_name')->nullable();
            $table->string('tshirt_size')->nullable();
            $table->string('coach_name')->nullable();
            
            $table->date('date_emission')->nullable();
            $table->date('date_validity')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
