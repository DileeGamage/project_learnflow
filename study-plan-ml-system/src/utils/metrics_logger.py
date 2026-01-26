"""
Metrics Logger - Save model training metrics to Laravel database
"""
import mysql.connector
from datetime import datetime
import json
import os
from typing import Dict, Any, Optional
from pathlib import Path

# Try to load python-dotenv if available
try:
    from dotenv import load_dotenv
    # Load .env file from the project root
    env_path = Path(__file__).parent.parent.parent / '.env'
    if env_path.exists():
        load_dotenv(env_path)
except ImportError:
    pass

class MetricsLogger:
    """
    Logger to save ML model training metrics to the Laravel database.
    """
    
    def __init__(self):
        """Initialize database connection parameters from environment or defaults."""
        self.config = {
            'host': os.getenv('DB_HOST', 'localhost'),
            'user': os.getenv('DB_USERNAME', 'root'),
            'password': os.getenv('DB_PASSWORD', ''),
            'database': os.getenv('DB_DATABASE', 'laravel')
        }
    
    def _get_connection(self):
        """Create and return a database connection."""
        try:
            return mysql.connector.connect(**self.config)
        except mysql.connector.Error as err:
            print(f"Database connection error: {err}")
            print("Metrics will not be saved to database.")
            return None
    
    def log_training_metrics(
        self,
        model_name: str,
        model_type: str,
        metrics: Dict[str, Any],
        feature_importance: Optional[list] = None,
        hyperparameters: Optional[dict] = None,
        version: str = "1.0",
        notes: Optional[str] = None
    ) -> bool:
        """
        Save training metrics to the database.
        
        Args:
            model_name: Name of the model ('knowledge_mastery', 'user_profiling', etc.)
            model_type: Type of algorithm ('xgboost', 'lightgbm', 'random_forest', etc.)
            metrics: Dictionary containing training metrics
            feature_importance: List of tuples (feature_name, importance_score)
            hyperparameters: Dictionary of model hyperparameters
            version: Model version
            notes: Additional notes about the training
            
        Returns:
            bool: True if successful, False otherwise
        """
        conn = self._get_connection()
        if not conn:
            return False
        
        try:
            cursor = conn.cursor()
            
            # Prepare feature importance JSON
            feature_importance_json = None
            if feature_importance:
                feature_importance_json = json.dumps(dict(feature_importance))
            
            # Prepare hyperparameters JSON
            hyperparameters_json = None
            if hyperparameters:
                hyperparameters_json = json.dumps(hyperparameters)
            
            # Extract metrics based on model type
            # Regression metrics
            mse = metrics.get('mse')
            rmse = metrics.get('rmse')
            mae = metrics.get('mae')
            r2_score = metrics.get('r2_score')
            
            # Classification metrics
            accuracy = metrics.get('accuracy')
            precision = metrics.get('precision')
            recall = metrics.get('recall')
            f1_score = metrics.get('f1_score')
            
            # Training details
            training_samples = metrics.get('training_samples', metrics.get('train_size'))
            test_samples = metrics.get('test_samples', metrics.get('test_size'))
            test_size_ratio = metrics.get('test_size_ratio', 0.20)
            
            # Confusion matrix for classification
            confusion_matrix = metrics.get('confusion_matrix')
            confusion_matrix_json = json.dumps(confusion_matrix) if confusion_matrix else None
            
            # Insert query
            query = """
            INSERT INTO model_training_metrics 
            (model_name, model_type, version, 
             accuracy, `precision`, recall, f1_score,
             mse, rmse, mae, r2_score,
             training_samples, test_samples, test_size,
             hyperparameters, feature_importance, confusion_matrix,
             trained_at, notes, created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            """
            
            now = datetime.now()
            values = (
                model_name,
                model_type,
                version,
                accuracy,
                precision,
                recall,
                f1_score,
                mse,
                rmse,
                mae,
                r2_score,
                training_samples,
                test_samples,
                test_size_ratio,
                hyperparameters_json,
                feature_importance_json,
                confusion_matrix_json,
                now,
                notes,
                now,
                now
            )
            
            cursor.execute(query, values)
            conn.commit()
            
            print(f"✅ Metrics saved to database (ID: {cursor.lastrowid})")
            
            cursor.close()
            conn.close()
            
            return True
            
        except mysql.connector.Error as err:
            print(f"Error saving metrics to database: {err}")
            if conn:
                conn.close()
            return False
        except Exception as e:
            print(f"Unexpected error saving metrics: {e}")
            if conn:
                conn.close()
            return False
    
    def get_latest_metrics(self, model_name: str, model_type: Optional[str] = None):
        """
        Retrieve the latest training metrics for a model.
        
        Args:
            model_name: Name of the model
            model_type: Optional specific model type
            
        Returns:
            dict: Latest metrics or None
        """
        conn = self._get_connection()
        if not conn:
            return None
        
        try:
            cursor = conn.cursor(dictionary=True)
            
            if model_type:
                query = """
                SELECT * FROM model_training_metrics 
                WHERE model_name = %s AND model_type = %s 
                ORDER BY trained_at DESC LIMIT 1
                """
                cursor.execute(query, (model_name, model_type))
            else:
                query = """
                SELECT * FROM model_training_metrics 
                WHERE model_name = %s 
                ORDER BY trained_at DESC LIMIT 1
                """
                cursor.execute(query, (model_name,))
            
            result = cursor.fetchone()
            
            cursor.close()
            conn.close()
            
            return result
            
        except mysql.connector.Error as err:
            print(f"Error retrieving metrics: {err}")
            if conn:
                conn.close()
            return None


# Global instance for easy import
metrics_logger = MetricsLogger()


def log_metrics(model_name: str, model_type: str, metrics: dict, **kwargs) -> bool:
    """
    Convenience function to log metrics.
    
    Usage:
        from utils.metrics_logger import log_metrics
        
        log_metrics('knowledge_mastery', 'xgboost', metrics, 
                   feature_importance=importance, 
                   hyperparameters=params)
    """
    return metrics_logger.log_training_metrics(
        model_name, model_type, metrics, **kwargs
    )
