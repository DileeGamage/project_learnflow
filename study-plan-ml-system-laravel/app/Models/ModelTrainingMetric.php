<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelTrainingMetric extends Model
{
    protected $fillable = [
        'model_name',
        'model_type',
        'version',
        'accuracy',
        'precision',
        'recall',
        'f1_score',
        'mse',
        'rmse',
        'mae',
        'r2_score',
        'training_samples',
        'test_samples',
        'test_size',
        'hyperparameters',
        'feature_importance',
        'confusion_matrix',
        'trained_at',
        'trained_by',
        'notes'
    ];

    protected $casts = [
        'hyperparameters' => 'array',
        'feature_importance' => 'array',
        'confusion_matrix' => 'array',
        'trained_at' => 'datetime',
        'accuracy' => 'float',
        'precision' => 'float',
        'recall' => 'float',
        'f1_score' => 'float',
        'mse' => 'float',
        'rmse' => 'float',
        'mae' => 'float',
        'r2_score' => 'float',
        'test_size' => 'float'
    ];

    /**
     * Get the latest metrics for a specific model
     */
    public static function getLatestMetrics($modelName, $modelType = null)
    {
        $query = static::where('model_name', $modelName);
        
        if ($modelType) {
            $query->where('model_type', $modelType);
        }
        
        return $query->orderBy('trained_at', 'desc')->first();
    }

    /**
     * Get all training history for a model
     */
    public static function getTrainingHistory($modelName, $limit = 10)
    {
        return static::where('model_name', $modelName)
            ->orderBy('trained_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Compare different model types
     */
    public static function compareModels($modelName)
    {
        return static::where('model_name', $modelName)
            ->orderBy('trained_at', 'desc')
            ->get()
            ->groupBy('model_type');
    }

    /**
     * Get best performing model by metric
     */
    public static function getBestModel($modelName, $metric = 'r2_score')
    {
        return static::where('model_name', $modelName)
            ->whereNotNull($metric)
            ->orderBy($metric, 'desc')
            ->first();
    }
}
