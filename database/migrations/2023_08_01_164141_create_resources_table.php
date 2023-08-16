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
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->string('attachment_type')->nullable();
            $table->string('file')->nullable();
            $table->text('text')->nullable();
            $table->string('url')->nullable();
            $table->string('type')->nullable();
            $table->date('date')->nullable();
            $table->date('date_end')->nullable();
            $table->foreignId('athlete_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('author')->nullable();
            $table->boolean('is_protected')->nullable();
            $table->string('available_time_start')->nullable();
            $table->string('available_weekdays')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
