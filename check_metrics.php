<?php

require __DIR__ . '/study-plan-ml-system-laravel/vendor/autoload.php';

use App\Models\ModelTrainingMetric;

$app = require_once __DIR__ . '/study-plan-ml-system-laravel/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n" . str_repeat('=', 70) . "\n";
echo "MODEL TRAINING METRICS - CURRENT RESULTS\n";
echo str_repeat('=', 70) . "\n\n";

// Knowledge Mastery Models
echo "📊 KNOWLEDGE MASTERY MODELS (Regression)\n";
echo str_repeat('-', 70) . "\n";

$knowledgeModels = ModelTrainingMetric::where('model_name', 'knowledge_mastery')
    ->orderBy('r2_score', 'desc')
    ->get();

foreach ($knowledgeModels as $model) {
    echo "\n✓ {$model->model_type}\n";
    echo "  RMSE: {$model->rmse} (Lower is better)\n";
    echo "  MAE:  {$model->mae}\n";
    echo "  R²:   {$model->r2_score} (Higher is better, max 1.0)\n";
    echo "  Samples: {$model->training_samples} training, {$model->test_samples} testing\n";
    echo "  Trained: {$model->trained_at->format('Y-m-d H:i:s')}\n";
}

// Best Knowledge Mastery Model
$bestKnowledge = ModelTrainingMetric::getBestModel('knowledge_mastery', 'r2_score');
echo "\n🏆 BEST KNOWLEDGE MASTERY MODEL: {$bestKnowledge->model_type}\n";
echo "   R² Score: {$bestKnowledge->r2_score}\n";

echo "\n" . str_repeat('=', 70) . "\n";

// User Profiling Models
echo "\n📊 USER PROFILING MODELS (Classification)\n";
echo str_repeat('-', 70) . "\n";

$profilingModels = ModelTrainingMetric::where('model_name', 'user_profiling')
    ->orderBy('accuracy', 'desc')
    ->get();

foreach ($profilingModels as $model) {
    echo "\n✓ {$model->model_type}\n";
    echo "  Accuracy:  {$model->accuracy} (0-1 scale)\n";
    echo "  Precision: {$model->precision}\n";
    echo "  Recall:    {$model->recall}\n";
    echo "  F1-Score:  {$model->f1_score}\n";
    echo "  Samples: {$model->training_samples} training, {$model->test_samples} testing\n";
    echo "  Trained: {$model->trained_at->format('Y-m-d H:i:s')}\n";
}

if ($profilingModels->count() > 0) {
    $bestProfiling = ModelTrainingMetric::getBestModel('user_profiling', 'accuracy');
    echo "\n🏆 BEST USER PROFILING MODEL: {$bestProfiling->model_type}\n";
    echo "   Accuracy: {$bestProfiling->accuracy}\n";
}

echo "\n" . str_repeat('=', 70) . "\n";

// Model Comparison
echo "\n📈 MODEL COMPARISON\n";
echo str_repeat('-', 70) . "\n";

echo "\nKnowledge Mastery - Performance Ranking (by R²):\n";
$ranking = 1;
foreach ($knowledgeModels as $model) {
    $performance = $model->r2_score >= 0.85 ? '⭐ Excellent' : ($model->r2_score >= 0.7 ? '✓ Good' : '⚠ Needs Work');
    echo "  {$ranking}. {$model->model_type}: R² = {$model->r2_score} {$performance}\n";
    $ranking++;
}

echo "\n" . str_repeat('=', 70) . "\n";

// Summary Statistics
echo "\n📊 SUMMARY STATISTICS\n";
echo str_repeat('-', 70) . "\n";

$totalMetrics = ModelTrainingMetric::count();
$avgR2 = ModelTrainingMetric::where('model_name', 'knowledge_mastery')->avg('r2_score');
$avgAccuracy = ModelTrainingMetric::where('model_name', 'user_profiling')->avg('accuracy');

echo "\nTotal Training Sessions Recorded: {$totalMetrics}\n";
echo "Average R² Score (Knowledge Mastery): " . number_format($avgR2, 4) . "\n";
if ($avgAccuracy) {
    echo "Average Accuracy (User Profiling): " . number_format($avgAccuracy, 4) . "\n";
}

echo "\n" . str_repeat('=', 70) . "\n";
echo "\n✅ All metrics retrieved successfully!\n";
echo "\n💡 Next steps:\n";
echo "   - Train new models to update metrics\n";
echo "   - Access via API: http://localhost:8000/api/model-metrics/latest/knowledge_mastery\n";
echo "   - View dashboard: http://localhost:8000/admin/model-metrics\n\n";
