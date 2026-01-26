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
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('quiz_id')->after('question_number')->constrained()->onDelete('cascade');
            $table->text('question_text')->after('quiz_id');
            $table->string('question_type', 50)->after('question_text');
            $table->json('options')->nullable()->after('question_type');
            $table->string('correct_answer')->after('options');
            $table->text('explanation')->nullable()->after('correct_answer');
            $table->integer('points')->default(1)->after('explanation');
            $table->string('topic')->nullable()->after('points');
            $table->string('difficulty', 20)->default('medium')->after('topic');
            $table->json('metadata')->nullable()->after('difficulty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['quiz_id']);
            $table->dropColumn([
                'quiz_id',
                'question_text',
                'question_type',
                'options',
                'correct_answer',
                'explanation',
                'points',
                'topic',
                'difficulty',
                'metadata'
            ]);
        });
    }
};
