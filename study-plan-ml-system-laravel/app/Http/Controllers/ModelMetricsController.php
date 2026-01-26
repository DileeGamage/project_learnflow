<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ModelTrainingMetric;
use Illuminate\Http\Request;

class ModelMetricsController extends Controller
{
    /**
     * Display all model training metrics
     */
    public function index()
    {
        $metrics = ModelTrainingMetric::orderBy('trained_at', 'desc')
            ->paginate(20);
        
        return view('admin.model-metrics.index', compact('metrics'));
    }
    
    /**
     * Get latest metrics for a specific model
     */
    public function getLatestMetrics($modelName)
    {
        $metrics = ModelTrainingMetric::getLatestMetrics($modelName);
        
        if (!$metrics) {
            return response()->json([
                'error' => 'No metrics found for this model'
            ], 404);
        }
        
        return response()->json($metrics);
    }
    
    /**
     * Get training history for a model
     */
    public function getHistory($modelName)
    {
        $history = ModelTrainingMetric::getTrainingHistory($modelName, 10);
        
        return response()->json($history);
    }
    
    /**
     * Compare different model types
     */
    public function compareModels($modelName)
    {
        $comparison = ModelTrainingMetric::compareModels($modelName);
        
        return response()->json($comparison);
    }
    
    /**
     * Get best performing model
     */
    public function getBestModel($modelName, Request $request)
    {
        $metric = $request->get('metric', 'r2_score');
        $bestModel = ModelTrainingMetric::getBestModel($modelName, $metric);
        
        if (!$bestModel) {
            return response()->json([
                'error' => 'No models found'
            ], 404);
        }
        
        return response()->json($bestModel);
    }
    
    /**
     * Display detailed metrics for a specific training session
     */
    public function show($id)
    {
        $metric = ModelTrainingMetric::findOrFail($id);
        
        return view('admin.model-metrics.show', compact('metric'));
    }
    
    /**
     * API endpoint to get all metrics as JSON
     */
    public function apiIndex(Request $request)
    {
        $query = ModelTrainingMetric::query();
        
        // Filter by model name
        if ($request->has('model_name')) {
            $query->where('model_name', $request->model_name);
        }
        
        // Filter by model type
        if ($request->has('model_type')) {
            $query->where('model_type', $request->model_type);
        }
        
        // Order by specified column
        $orderBy = $request->get('order_by', 'trained_at');
        $orderDir = $request->get('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);
        
        $metrics = $query->paginate($request->get('per_page', 20));
        
        return response()->json($metrics);
    }
    
    /**
     * Dashboard view with all model comparisons
     */
    public function dashboard()
    {
        $knowledgeMastery = ModelTrainingMetric::where('model_name', 'knowledge_mastery')
            ->orderBy('trained_at', 'desc')
            ->limit(5)
            ->get();
            
        $userProfiling = ModelTrainingMetric::where('model_name', 'user_profiling')
            ->orderBy('trained_at', 'desc')
            ->limit(5)
            ->get();
        
        $latestKnowledge = ModelTrainingMetric::getLatestMetrics('knowledge_mastery');
        $latestProfiling = ModelTrainingMetric::getLatestMetrics('user_profiling');
        
        return view('admin.model-metrics.dashboard', compact(
            'knowledgeMastery',
            'userProfiling',
            'latestKnowledge',
            'latestProfiling'
        ));
    }
}
