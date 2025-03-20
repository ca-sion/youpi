<?php

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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->date('published_on')->nullable();
            $table->date('expires_on')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();

            $table->json('sections')->nullable();

            $table->string('salutation')->nullable();
            $table->string('signature')->nullable();
            $table->text('signature_data')->nullable();
            $table->string('author')->nullable();

            $table->text('introduction')->nullable();
            $table->text('outro')->nullable();
            $table->json('content')->nullable();
            $table->json('recipient')->nullable();
            $table->json('travel_data')->nullable();

            $table->boolean('has_sponsors')->nullable();
            $table->boolean('is_private')->nullable();

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
        Schema::dropIfExists('documents');
    }
};
