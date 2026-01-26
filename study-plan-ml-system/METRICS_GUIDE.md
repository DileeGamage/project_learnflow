# Model Training Metrics System

## Overview
This system automatically captures and stores ML model training metrics in the database for tracking model performance over time.

## Database Schema

The `model_training_metrics` table stores:

### Classification Metrics (User Profiling Models)
- **Accuracy**: Overall prediction accuracy
- **Precision**: Positive prediction accuracy
- **Recall**: True positive rate
- **F1-Score**: Harmonic mean of precision and recall
- **Confusion Matrix**: Detailed classification results

### Regression Metrics (Knowledge Mastery Models)
- **MSE**: Mean Squared Error
- **RMSE**: Root Mean Squared Error (lower is better)
- **MAE**: Mean Absolute Error
- **R² Score**: Coefficient of determination (higher is better, max 1.0)

### Training Details
- Training/test sample counts
- Hyperparameters used
- Feature importance rankings
- Training timestamp
- Model version

## Setup

### 1. Install Required Python Packages

```bash
cd study-plan-ml-system
pip install python-dotenv mysql-connector-python
```

Or install all requirements:

```bash
pip install -r requirements.txt
```

### 2. Configure Database Connection

Edit `study-plan-ml-system/.env`:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Run Database Migration

```bash
cd study-plan-ml-system-laravel
php artisan migrate
```

## Usage

### Training Models with Metrics Logging

The training scripts automatically save metrics to the database:

```bash
# Train Knowledge Mastery Models
cd study-plan-ml-system
python src/training/train_mastery.py
```

This will:
1. Train the model
2. Calculate all metrics
3. Save to database automatically
4. Print metrics to console

### View Metrics in Laravel

#### API Endpoints

```bash
# Get latest metrics for knowledge mastery model
GET /api/model-metrics/latest/knowledge_mastery

# Get training history
GET /api/model-metrics/history/knowledge_mastery

# Compare different model types
GET /api/model-metrics/compare/knowledge_mastery

# Get best model by R² score
GET /api/model-metrics/best/knowledge_mastery?metric=r2_score
```

#### Web Dashboard

Visit: `http://localhost:8000/admin/model-metrics`

This dashboard shows:
- Latest training results for all models
- Historical performance trends
- Model comparisons
- Feature importance visualizations

### Manual Metrics Logging

You can also log metrics manually from Python:

```python
from utils.metrics_logger import log_metrics

# After training your model
metrics = {
    'rmse': 5.23,
    'mae': 4.12,
    'r2_score': 0.856,
    'training_samples': 400,
    'test_samples': 100
}

log_metrics(
    model_name='knowledge_mastery',
    model_type='xgboost',
    metrics=metrics,
    feature_importance=[('last_exam_score', 0.35), ('study_hours', 0.23)],
    hyperparameters={'n_estimators': 100, 'max_depth': 6},
    notes='Trained on synthetic data'
)
```

## Understanding the Metrics

### For Regression Models (Knowledge Mastery)

- **RMSE < 10**: Excellent performance
- **RMSE 10-15**: Good performance
- **RMSE > 15**: Needs improvement

- **R² > 0.8**: Excellent fit
- **R² 0.6-0.8**: Good fit
- **R² < 0.6**: Poor fit

### For Classification Models (User Profiling)

- **Accuracy > 0.9**: Excellent
- **Accuracy 0.8-0.9**: Good
- **Accuracy < 0.8**: Needs improvement

- **F1-Score**: Balance of precision and recall
  - > 0.85: Excellent
  - 0.70-0.85: Good
  - < 0.70: Needs work

## Retrieving Metrics in Laravel

```php
use App\Models\ModelTrainingMetric;

// Get latest metrics
$latest = ModelTrainingMetric::getLatestMetrics('knowledge_mastery');

// Get best performing model
$best = ModelTrainingMetric::getBestModel('knowledge_mastery', 'r2_score');

// Get training history
$history = ModelTrainingMetric::getTrainingHistory('knowledge_mastery', 10);

// Compare model types
$comparison = ModelTrainingMetric::compareModels('knowledge_mastery');
```

## Files Modified/Created

### Created:
- `study-plan-ml-system/src/utils/metrics_logger.py` - Python metrics logger
- `study-plan-ml-system/.env` - Database configuration
- `study-plan-ml-system-laravel/database/migrations/2025_11_11_075917_create_model_training_metrics_table.php`
- `study-plan-ml-system-laravel/app/Models/ModelTrainingMetric.php`
- `study-plan-ml-system-laravel/app/Http/Controllers/ModelMetricsController.php`

### Modified:
- `study-plan-ml-system/src/training/train_mastery.py` - Added metrics logging
- `study-plan-ml-system/src/training/train_profiling.py` - Added metrics logging
- `study-plan-ml-system/src/models/user_profiling.py` - Returns metrics
- `study-plan-ml-system/requirements.txt` - Added dependencies
- `study-plan-ml-system-laravel/routes/web.php` - Added metrics routes

## Troubleshooting

### "Module 'mysql.connector' not found"
```bash
pip install mysql-connector-python
```

### "Connection refused" or database errors
- Check that MySQL is running
- Verify credentials in `.env` file
- Ensure database 'laravel' exists

### Metrics not saving
- Check console output for error messages
- Verify database connection in `.env`
- Ensure migration has been run

## Next Steps

1. Create visualization dashboards for metrics
2. Add email alerts for poor model performance
3. Implement A/B testing framework
4. Add model versioning and rollback capabilities
