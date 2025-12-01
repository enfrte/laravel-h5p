<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('h5p_responses', function (Blueprint $table) {
            $table->id();
            // store interaction id as plain integer (no FK constraint)
            $table->string('context_parent_id');
            // store user and course as strings (no users/courses tables)
            $table->string('student');
            $table->string('course');

            $table->string('question_id');
            $table->text('question_text')->nullable();
            $table->string('interaction_type');
            $table->boolean('is_correct')->nullable();
            $table->json('response')->nullable();
            $table->json('correct_responses_pattern')->nullable();
            $table->timestamps();
            
            $table->index(['context_parent_id', 'question_id']);
            $table->index('is_correct');
            $table->index('student');
            $table->index('course');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('h5p_responses');
    }
};