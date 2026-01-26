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
        Schema::table('quizzes', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('quizzes', 'content_analysis')) {
                $table->json('content_analysis')->nullable()->after('questions');
            }
            if (!Schema::hasColumn('quizzes', 'total_questions')) {
                $table->integer('total_questions')->after('content_analysis');
            }
            if (!Schema::hasColumn('quizzes', 'estimated_time')) {
                $table->integer('estimated_time')->after('total_questions');
            }
            if (!Schema::hasColumn('quizzes', 'difficulty_level')) {
                $table->enum('difficulty_level', ['easy', 'medium', 'hard'])->default('medium')->after('estimated_time');
            }
            if (!Schema::hasColumn('quizzes', 'quiz_metadata')) {
                $table->json('quiz_metadata')->nullable()->after('difficulty_level');
            }
            if (!Schema::hasColumn('quizzes', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('quiz_metadata');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn([
                'content_analysis',
                'total_questions', 
                'estimated_time',
                'difficulty_level',
                'quiz_metadata',
                'is_active'
            ]);
        });
    }
};
