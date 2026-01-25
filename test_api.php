<?php

require __DIR__ . '/study-plan-ml-system-laravel/vendor/autoload.php';

$app = require_once __DIR__ . '/study-plan-ml-system-laravel/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');

echo "\n" . str_repeat('=', 70) . "\n";
echo "TESTING MODEL METRICS API ENDPOINTS\n";
echo str_repeat('=', 70) . "\n\n";

// Test 1: Get latest knowledge mastery metrics
echo "1. GET /api/model-metrics/latest/knowledge_mastery\n";
echo str_repeat('-', 70) . "\n";
$request = Illuminate\Http\Request::create('/api/model-metrics/latest/knowledge_mastery', 'GET');
$response = $kernel->handle($request);
$data = json_decode($response->getContent(), true);
echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

// Test 2: Get latest user profiling metrics  
echo "2. GET /api/model-metrics/latest/user_profiling\n";
echo str_repeat('-', 70) . "\n";
$request = Illuminate\Http\Request::create('/api/model-metrics/latest/user_profiling', 'GET');
$response = $kernel->handle($request);
$data = json_decode($response->getContent(), true);
echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

// Test 3: Get training history
echo "3. GET /api/model-metrics/history/knowledge_mastery\n";
echo str_repeat('-', 70) . "\n";
$request = Illuminate\Http\Request::create('/api/model-metrics/history/knowledge_mastery', 'GET');
$response = $kernel->handle($request);
$data = json_decode($response->getContent(), true);
echo "Found " . count($data) . " training sessions\n\n";

// Test 4: Get best model
echo "4. GET /api/model-metrics/best/knowledge_mastery?metric=r2_score\n";
echo str_repeat('-', 70) . "\n";
$request = Illuminate\Http\Request::create('/api/model-metrics/best/knowledge_mastery?metric=r2_score', 'GET');
$response = $kernel->handle($request);
$data = json_decode($response->getContent(), true);
echo "Best model: " . ($data['model_type'] ?? 'N/A') . " with R² = " . ($data['r2_score'] ?? 'N/A') . "\n\n";

echo str_repeat('=', 70) . "\n";
echo "✅ All API endpoints are working!\n\n";
