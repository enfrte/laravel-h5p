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
            $table->foreignId('interaction_id')
                ->constrained('h5p_interactions')
                ->onDelete('cascade');
            $table->string('question_id');
            $table->text('question_text')->nullable();
            $table->string('interaction_type');
            $table->boolean('is_correct')->nullable();
            $table->json('response')->nullable();
            $table->json('correct_responses_pattern')->nullable();
            $table->timestamps();
            
            $table->index(['interaction_id', 'question_id']);
            $table->index('is_correct');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('h5p_responses');
    }
};