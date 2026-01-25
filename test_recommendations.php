<?php

// Quick test script for Smart Recommendations
require __DIR__ . '/study-plan-ml-system-laravel/vendor/autoload.php';

$app = require_once __DIR__ . '/study-plan-ml-system-laravel/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\SmartRecommendationService;
use App\Models\QuizAttempt;

echo "\n" . str_repeat('=', 70) . "\n";
echo "TESTING SMART RECOMMENDATION SERVICE\n";
echo str_repeat('=', 70) . "\n\n";

try {
    // Get a quiz attempt
    $attempt = QuizAttempt::with('quiz')->first();
    
    if (!$attempt) {
        echo "❌ No quiz attempts found in database\n\n";
        exit;
    }
    
    echo "✓ Found quiz attempt ID: {$attempt->id}\n";
    echo "  User ID: {$attempt->user_id}\n";
    echo "  Score: {$attempt->score}/{$attempt->total_questions} ({$attempt->percentage}%)\n";
    echo "  Quiz: {$attempt->quiz->title}\n\n";
    
    // Test the service
    echo "Generating smart recommendations...\n\n";
    
    $service = new SmartRecommendationService();
    $recommendations = $service->generateRecommendations(
        $attempt->user_id,
        $attempt->id
    );
    
    echo "✅ SUCCESS! Recommendations generated\n\n";
    
    // Display weak topics
    if (!empty($recommendations['weak_topics'])) {
        echo "📚 WEAK TOPICS IDENTIFIED:\n";
        echo str_repeat('-', 70) . "\n";
        
        foreach ($recommendations['weak_topics'] as $topic) {
            echo "\n🎯 {$topic['topic']}\n";
            echo "   Priority: {$topic['priority_label']}\n";
            echo "   Mastery: {$topic['mastery_score']}%\n";
            echo "   Questions: {$topic['questions_correct']}/{$topic['questions_attempted']} correct\n";
            echo "   Action: {$topic['action']}\n";
            echo "   Time Needed: {$topic['estimated_time']}\n";
        }
    } else {
        echo "🎉 No weak topics found!\n";
    }
    
    echo "\n" . str_repeat('-', 70) . "\n\n";
    
    // Display revision strategy
    if (!empty($recommendations['revision_strategy'])) {
        echo "📖 REVISION STRATEGY:\n";
        echo str_repeat('-', 70) . "\n";
        
        foreach ($recommendations['revision_strategy'] as $strategy) {
            echo "\n{$strategy['title']}\n";
            echo "   {$strategy['description']}\n";
            echo "   Action: {$strategy['action']}\n";
            echo "   Daily Time: {$strategy['daily_time']}\n";
            
            if (isset($strategy['expected_improvement'])) {
                echo "   Expected Result: {$strategy['expected_improvement']}\n";
            }
        }
    }
    
    echo "\n" . str_repeat('-', 70) . "\n\n";
    
    // Display performance insights
    if (!empty($recommendations['performance_insights'])) {
        $insights = $recommendations['performance_insights'];
        
        echo "📊 PERFORMANCE INSIGHTS:\n";
        echo str_repeat('-', 70) . "\n";
        echo "\n{$insights['message']}\n";
        
        if (isset($insights['overall_mastery'])) {
            echo "\nOverall Mastery: {$insights['overall_mastery']}%\n";
        }
        
        if (!empty($insights['strongest_topics'])) {
            echo "\n✅ Strongest Topics:\n";
            foreach ($insights['strongest_topics'] as $topic) {
                echo "   • {$topic['name']}: {$topic['score']}\n";
            }
        }
        
        if (!empty($insights['weakest_topics'])) {
            echo "\n⚠️  Weakest Topics:\n";
            foreach ($insights['weakest_topics'] as $topic) {
                echo "   • {$topic['name']}: {$topic['score']}\n";
            }
        }
    }
    
    echo "\n" . str_repeat('=', 70) . "\n";
    echo "✅ TEST COMPLETED SUCCESSFULLY!\n";
    echo str_repeat('=', 70) . "\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n\n";
}
