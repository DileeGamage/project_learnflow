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
        // Add topic_analysis field to quizzes table to store topic-level performance
        Schema::table('quizzes', function (Blueprint $table) {
            $table->json('topic_analysis')->nullable()->after('quiz_metadata');
        });
        
        // Add detailed_results to quiz_attempts to track per-question performance
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->json('detailed_results')->nullable()->after('answers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('topic_analysis');
        });
        
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn('detailed_results');
        });
    }
};
