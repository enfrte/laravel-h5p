<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('h5p_interactions', function (Blueprint $table) {
            $table->id();
            $table->string('student_id');
            $table->string('content_id');
            $table->json('interaction_data')->nullable();
            $table->integer('score')->nullable();
            $table->integer('max_score')->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamp('first_interaction_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->unique(['student_id', 'content_id']);
            $table->index(['student_id', 'completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('h5p_interactions');
    }
};