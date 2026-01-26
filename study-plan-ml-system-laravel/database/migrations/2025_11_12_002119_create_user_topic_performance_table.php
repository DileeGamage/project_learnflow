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
        Schema::create('user_topic_performance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('note_id');
            $table->string('topic_name'); // e.g., "Binary Trees", "Sorting Algorithms"
            $table->integer('questions_attempted')->default(0);
            $table->integer('questions_correct')->default(0);
            $table->decimal('mastery_score', 5, 2)->default(0); // 0-100
            $table->enum('mastery_level', ['weak', 'developing', 'proficient', 'mastered'])->default('weak');
            $table->timestamp('last_practiced_at')->nullable();
            $table->integer('consecutive_correct')->default(0);
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('note_id')->references('id')->on('notes')->onDelete('cascade');
            
            $table->unique(['user_id', 'note_id', 'topic_name']);
            $table->index(['user_id', 'mastery_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_topic_performance');
    }
};
