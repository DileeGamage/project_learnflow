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
        Schema::create('user_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('total_points')->default(0);
            $table->integer('current_level')->default(1);
            $table->integer('points_in_level')->default(0);
            $table->integer('daily_streak')->default(0);
            $table->integer('weekly_streak')->default(0);
            $table->date('last_activity_date')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'total_points']);
            $table->index(['total_points', 'current_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_points');
    }
};