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
        Schema::create('model_training_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('model_name'); // 'knowledge_mastery', 'user_profiling', 'recommendation'
            $table->string('model_type'); // 'xgboost', 'lightgbm', 'random_forest', etc.
            $table->string('version')->default('1.0');
            
            // Classification Metrics (for User Profiling)
            $table->decimal('accuracy', 8, 4)->nullable();
            $table->decimal('precision', 8, 4)->nullable();
            $table->decimal('recall', 8, 4)->nullable();
            $table->decimal('f1_score', 8, 4)->nullable();
            
            // Regression Metrics (for Knowledge Mastery)
            $table->decimal('mse', 10, 4)->nullable();
            $table->decimal('rmse', 10, 4)->nullable();
            $table->decimal('mae', 10, 4)->nullable();
            $table->decimal('r2_score', 8, 4)->nullable();
            
            // Training Details
            $table->integer('training_samples')->nullable();
            $table->integer('test_samples')->nullable();
            $table->decimal('test_size', 3, 2)->default(0.20);
            $table->json('hyperparameters')->nullable();
            $table->json('feature_importance')->nullable();
            $table->json('confusion_matrix')->nullable(); // For classification models
            
            // Metadata
            $table->timestamp('trained_at');
            $table->string('trained_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['model_name', 'model_type', 'version']);
            $table->index('trained_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_training_metrics');
    }
};
