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
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('icon')->nullable();
            $table->string('category'); // performance, consistency, exploration, social, milestones
            $table->integer('points_reward');
            $table->json('criteria'); // Conditions to unlock this achievement
            $table->boolean('is_active')->default(true);
            $table->integer('rarity_level')->default(1); // 1=Common, 2=Rare, 3=Epic, 4=Legendary
            $table->timestamps();

            $table->index(['category', 'is_active']);
            $table->index(['rarity_level', 'points_reward']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};