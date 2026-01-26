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
        Schema::create('daily_challenges', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description');
            $table->string('challenge_type'); // quiz_score, quiz_count, study_time, streak, perfect_score
            $table->json('requirements'); // Target values
            $table->integer('points_reward');
            $table->date('challenge_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['challenge_date', 'is_active']);
            $table->index(['challenge_type', 'challenge_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_challenges');
    }
};