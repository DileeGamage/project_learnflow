# Model Training Metrics - Quick Reference

## What Was Implemented

✅ **Database Table**: `model_training_metrics` - Stores all training metrics
✅ **Python Logger**: Automatically saves metrics during training
✅ **Laravel Model**: Easy access to metrics in your app
✅ **API Endpoints**: RESTful API for metrics retrieval
✅ **Controller**: Pre-built methods for common queries

## Key Metrics Tracked

### Regression Models (Knowledge Mastery)
- **RMSE** (Root Mean Squared Error) - Lower is better
- **MAE** (Mean Absolute Error) - Lower is better  
- **R² Score** - Higher is better (0-1 range)
- **MSE** (Mean Squared Error)

### Classification Models (User Profiling)
- **Accuracy** - Overall correct predictions (0-1)
- **Precision** - Positive prediction accuracy (0-1)
- **Recall** - True positive rate (0-1)
- **F1-Score** - Balanced measure (0-1)

### Additional Data
- Feature importance rankings
- Hyperparameters used
- Training/test sample counts
- Confusion matrix (for classification)
- Training timestamp

## How to Use

### 1. View Latest Metrics via API

```bash
# Get latest knowledge mastery metrics
curl http://localhost:8000/api/model-metrics/latest/knowledge_mastery

# Get user profiling metrics
curl http://localhost:8000/api/model-metrics/latest/user_profiling

# Get training history
curl http://localhost:8000/api/model-metrithe way that earlier cs/history/knowledge_mastery

# Compare models
curl http://localhost:8000/api/model-metrics/compare/knowledge_mastery
```

### 2. View in Laravel

```php
use App\Models\ModelTrainingMetric;

// Get latest
$metrics = ModelTrainingMetric::getLatestMetrics('knowledge_mastery');
echo "RMSE: " . $metrics->rmse;
echo "R²: " . $metrics->r2_score;

// Get best model
$best = ModelTrainingMetric::getBestModel('knowledge_mastery', 'r2_score');
```

### 3. Train Models (Auto-saves metrics)

```bash
cd study-plan-ml-system
python src/training/train_mastery.py
```

Output will show:
```
Knowledge Mastery Model Training Results:
RMSE: 5.2341
MAE: 4.1234
R² Score: 0.8567
✅ Metrics saved to database (ID: 1)
```

### 4. Test the System

```bash
cd study-plan-ml-system
python src/utils/test_metrics_logger.py
```

This creates sample metrics in the database.

## Interpreting Metrics

### Knowledge Mastery (Regression)

| Metric | Excellent | Good | Needs Work |
|--------|-----------|------|------------|
| RMSE   | < 5       | 5-10 | > 10       |
| MAE    | < 4       | 4-8  | > 8        |
| R²     | > 0.85    | 0.7-0.85 | < 0.7  |

### User Profiling (Classification)

| Metric | Excellent | Good | Needs Work |
|--------|-----------|------|------------|
| Accuracy | > 0.90  | 0.80-0.90 | < 0.80  |
| F1-Score | > 0.85  | 0.70-0.85 | < 0.70  |
| Precision| > 0.85  | 0.75-0.85 | < 0.75  |

## Files Created/Modified

### New Files:
1. `study-plan-ml-system/src/utils/metrics_logger.py` - Metrics logger
2. `study-plan-ml-system/src/utils/test_metrics_logger.py` - Test script
3. `study-plan-ml-system/.env` - Database config
4. `study-plan-ml-system/METRICS_GUIDE.md` - Full documentation
5. `study-plan-ml-system-laravel/app/Models/ModelTrainingMetric.php`
6. `study-plan-ml-system-laravel/app/Http/Controllers/ModelMetricsController.php`
7. Migration: `2025_11_11_075917_create_model_training_metrics_table.php`

### Modified Files:
1. `study-plan-ml-system/src/training/train_mastery.py` - Added logging
2. `study-plan-ml-system/src/training/train_profiling.py` - Added logging
3. `study-plan-ml-system/src/models/user_profiling.py` - Returns metrics
4. `study-plan-ml-system/requirements.txt` - Added dependencies
5. `study-plan-ml-system-laravel/routes/web.php` - Added routes

## Next Steps

1. **Test the system**: Run `python src/utils/test_metrics_logger.py`
2. **Train models**: Run your training scripts - metrics auto-save
3. **View metrics**: Check API endpoints or database
4. **Create dashboard**: Build visualizations for metrics trends
5. **Set up alerts**: Email when model performance drops

## Quick Database Check

```sql
-- View all metrics
SELECT model_name, model_type, rmse, r2_score, accuracy, trained_at 
FROM model_training_metrics 
ORDER BY trained_at DESC;

-- Best knowledge mastery model
SELECT * FROM model_training_metrics 
WHERE model_name = 'knowledge_mastery' 
ORDER BY r2_score DESC 
LIMIT 1;

-- Compare model types
SELECT model_type, AVG(rmse) as avg_rmse, AVG(r2_score) as avg_r2
FROM model_training_metrics 
WHERE model_name = 'knowledge_mastery'
GROUP BY model_type;
```

## API Endpoints Summary

| Endpoint | Description |
|----------|-------------|
| `GET /api/model-metrics/` | List all metrics |
| `GET /api/model-metrics/latest/{model}` | Latest for a model |
| `GET /api/model-metrics/history/{model}` | Training history |
| `GET /api/model-metrics/compare/{model}` | Compare types |
| `GET /api/model-metrics/best/{model}` | Best by metric |

## Web Dashboard (Future)

Visit: `http://localhost:8000/admin/model-metrics`

Will show:
- Latest metrics for all models
- Performance trends over time
- Model comparisons
- Feature importance charts
