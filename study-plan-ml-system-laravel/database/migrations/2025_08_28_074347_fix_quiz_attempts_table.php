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
        // Drop the existing incomplete quiz_attempts table
        Schema::dropIfExists('quiz_attempts');
        
        // Create the correct quiz_attempts table
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->nullable(); // Allow null for guest users
            $table->json('answers'); // Store user answers as JSON
            $table->integer('score');
            $table->integer('total_questions');
            $table->decimal('percentage', 5, 2);
            $table->integer('time_taken')->nullable(); // in seconds
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['quiz_id', 'user_id']);
            $table->index(['user_id', 'completed_at']);
            $table->index('percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
        
        // Recreate the old incomplete table (for rollback purposes)
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
