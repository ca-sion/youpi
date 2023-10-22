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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->string('location')->nullable();

            $table->string('status')->nullable();
            $table->json('types')->nullable();
            $table->json('athlete_categories')->nullable();
            $table->json('athlete_category_groups')->nullable();

            $table->boolean('has_deadline')->nullable();
            $table->string('deadline_type')->nullable();
            $table->dateTime('deadline_at')->nullable();
            $table->string('deadline_text')->nullable();
            $table->string('deadline_url')->nullable();

            $table->boolean('has_qualified')->nullable();
            $table->string('qualified_type')->nullable();
            $table->string('qualified_url')->nullable();
            $table->text('qualified_list')->nullable();
            $table->text('qualified_already_received')->nullable();

            $table->boolean('has_convocation')->nullable();
            $table->string('convocation_type')->nullable();
            $table->text('convocation_text')->nullable();

            $table->boolean('has_entrants')->nullable();
            $table->string('entrants_type')->nullable();
            $table->text('entrants_text')->nullable();
            $table->string('entrants_url')->nullable();

            $table->boolean('has_provisional_timetable')->nullable();
            $table->string('provisional_timetable_url')->nullable();
            $table->string('provisional_timetable_text')->nullable();

            $table->boolean('has_final_timetable')->nullable();
            $table->string('final_timetable_url')->nullable();
            $table->string('final_timetable_text')->nullable();

            $table->boolean('has_publication')->nullable();
            $table->string('publication_url')->nullable();

            $table->boolean('has_rules')->nullable();
            $table->string('rules_url')->nullable();

            $table->boolean('has_trip')->nullable();
            $table->string('trip_type')->nullable();
            $table->string('trip_url')->nullable();
            $table->string('trip_text')->nullable();
            $table->string('trip_id')->nullable();

            $table->boolean('has_trainers_presences')->nullable();
            $table->boolean('trainers_presences_type')->nullable();
            $table->string('trainers_presences_id')->nullable();
            $table->string('trainers_presences_data')->nullable();

            $table->json('data')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
