"""
Test script to demonstrate metrics logging functionality
"""
import sys
import os
sys.path.append(os.path.join(os.path.dirname(__file__), '..'))

from utils.metrics_logger import log_metrics
import numpy as np

def test_metrics_logging():
    """Test the metrics logging system with sample data."""
    
    print("=" * 60)
    print("TESTING METRICS LOGGING SYSTEM")
    print("=" * 60)
    
    # Test 1: Log Knowledge Mastery Metrics (Regression)
    print("\n📊 Test 1: Logging Knowledge Mastery Model Metrics...")
    
    knowledge_metrics = {
        'mse': 27.45,
        'rmse': 5.24,
        'mae': 4.12,
        'r2_score': 0.856,
        'training_samples': 400,
        'test_samples': 100,
        'test_size_ratio': 0.2
    }
    
    feature_importance = [
        ('last_exam_score_percent', 0.3456),
        ('study_hours_per_day', 0.2341),
        ('exam_stress_level', 0.1823),
        ('study_efficiency', 0.1234),
        ('lifestyle_balance', 0.0856),
        ('revision_frequency_score', 0.0290)
    ]
    
    hyperparameters = {
        'n_estimators': 100,
        'max_depth': 6,
        'learning_rate': 0.1,
        'random_state': 42
    }
    
    success = log_metrics(
        model_name='knowledge_mastery',
        model_type='xgboost',
        metrics=knowledge_metrics,
        feature_importance=feature_importance,
        hyperparameters=hyperparameters,
        version='1.0',
        notes='Test training with synthetic data'
    )
    
    if success:
        print("✅ Knowledge Mastery metrics saved successfully!")
    else:
        print("❌ Failed to save Knowledge Mastery metrics")
    
    # Test 2: Log User Profiling Metrics (Classification)
    print("\n📊 Test 2: Logging User Profiling Model Metrics...")
    
    profiling_metrics = {
        'accuracy': 0.8750,
        'precision': 0.8623,
        'recall': 0.8512,
        'f1_score': 0.8567,
        'confusion_matrix': [[45, 5], [7, 43]],
        'training_samples': 320,
        'test_samples': 80,
        'test_size_ratio': 0.2
    }
    
    profiling_importance = [
        ('response_time', 0.4123),
        ('consistency', 0.3456),
        ('time_of_day', 0.2421)
    ]
    
    profiling_params = {
        'n_estimators': 100,
        'criterion': 'gini',
        'max_depth': None,
        'random_state': 42
    }
    
    success = log_metrics(
        model_name='user_profiling',
        model_type='random_forest',
        metrics=profiling_metrics,
        feature_importance=profiling_importance,
        hyperparameters=profiling_params,
        version='1.0',
        notes='Test classification with sample user data'
    )
    
    if success:
        print("✅ User Profiling metrics saved successfully!")
    else:
        print("❌ Failed to save User Profiling metrics")
    
    # Test 3: Log LightGBM Model
    print("\n📊 Test 3: Logging LightGBM Model Metrics...")
    
    lightgbm_metrics = {
        'mse': 25.12,
        'rmse': 5.01,
        'mae': 3.89,
        'r2_score': 0.871,
        'training_samples': 400,
        'test_samples': 100,
        'test_size_ratio': 0.2
    }
    
    success = log_metrics(
        model_name='knowledge_mastery',
        model_type='lightgbm',
        metrics=lightgbm_metrics,
        feature_importance=feature_importance,
        version='1.0',
        notes='Testing LightGBM alternative model'
    )
    
    if success:
        print("✅ LightGBM metrics saved successfully!")
    else:
        print("❌ Failed to save LightGBM metrics")
    
    print("\n" + "=" * 60)
    print("METRICS LOGGING TEST COMPLETED")
    print("=" * 60)
    print("\n📌 Next steps:")
    print("1. Check the database: SELECT * FROM model_training_metrics;")
    print("2. View in browser: http://localhost:8000/admin/model-metrics")
    print("3. API endpoint: http://localhost:8000/api/model-metrics/latest/knowledge_mastery")
    print()

if __name__ == '__main__':
    test_metrics_logging()
