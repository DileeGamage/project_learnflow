from sklearn.ensemble import GradientBoostingRegressor
from sklearn.neural_network import MLPRegressor
from sklearn.model_selection import train_test_split
from sklearn.metrics import mean_squared_error, r2_score, mean_absolute_error
from sklearn.preprocessing import StandardScaler
import pandas as pd
import numpy as np
import joblib
import logging

# Try to import XGBoost and LightGBM, fall back to alternatives if not available
try:
    from xgboost import XGBRegressor
    XGBOOST_AVAILABLE = True
except ImportError:
    XGBOOST_AVAILABLE = False
    logging.warning("XGBoost not available. Will use GradientBoostingRegressor as fallback.")

try:
    from lightgbm import LGBMRegressor
    LIGHTGBM_AVAILABLE = True
except ImportError:
    LIGHTGBM_AVAILABLE = False
    logging.warning("LightGBM not available. Will use GradientBoostingRegressor as fallback.")

class KnowledgeMasteryModel:
    """
    Knowledge Mastery Layer for predicting student performance and mastery levels.
    
    Purpose: Predict student performance and mastery levels using regression algorithms
    
    Algorithms Used:
    - XGBoost Regressor (Primary) - Gradient boosting for performance prediction
    - LightGBM (Alternative) - Fast gradient boosting for real-time responses
    - Gradient Boosting Trees (Fallback) - When XGBoost/LightGBM unavailable
    - Neural Networks (For large datasets)
    """
    
    def __init__(self, model_type='xgboost', random_state=42):
        """
        Initialize the Knowledge Mastery Model.
        
        Args:
            model_type (str): Type of model to use ('xgboost', 'lightgbm', 'gradient_boosting', 'neural_network')
            random_state (int): Random state for reproducibility
        """
        self.model_type = model_type
        self.random_state = random_state
        self.model = self._initialize_model()
        self.scaler = StandardScaler()
        self.is_trained = False
        
        # Feature names for interpretability
        self.feature_names = [
            'last_exam_score_percent',    # Current performance level
            'study_hours_per_day',        # Effort input
            'revision_frequency_score',   # Learning consistency
            'exam_stress_level',          # Performance under pressure
            'study_efficiency',           # Calculated: score/hours
            'lifestyle_balance'           # Sleep vs social media balance
        ]
    
    def _initialize_model(self):
        """Initialize the ML model based on the specified type."""
        if self.model_type == 'xgboost' and XGBOOST_AVAILABLE:
            return XGBRegressor(
                n_estimators=100,
                max_depth=6,
                learning_rate=0.1,
                random_state=self.random_state,
                eval_metric='rmse'
            )
        elif self.model_type == 'lightgbm' and LIGHTGBM_AVAILABLE:
            return LGBMRegressor(
                n_estimators=100,
                max_depth=6,
                learning_rate=0.1,
                random_state=self.random_state,
                verbose=-1
            )
        elif self.model_type == 'gradient_boosting':
            return GradientBoostingRegressor(
                n_estimators=100,
                max_depth=6,
                learning_rate=0.1,
                random_state=self.random_state
            )
        elif self.model_type == 'neural_network':
            return MLPRegressor(
                hidden_layer_sizes=(100, 50),
                max_iter=500,
                random_state=self.random_state,
                early_stopping=True
            )
        else:
            # Fallback to Gradient Boosting if requested model not available
            logging.warning(f"Model type '{self.model_type}' not available. Using GradientBoostingRegressor.")
            return GradientBoostingRegressor(
                n_estimators=100,
                max_depth=6,
                learning_rate=0.1,
                random_state=self.random_state
            )
    
    def engineer_features(self, data):
        """
        Engineer features for better prediction performance.
        
        Args:
            data (pd.DataFrame): Raw student data
            
        Returns:
            pd.DataFrame: Engineered features
        """
        # Create a copy to avoid modifying original data
        engineered_data = data.copy()
        
        # Ensure all numeric columns are actually numeric
        numeric_columns = ['last_exam_score_percent', 'study_hours_per_day', 'sleep_hours_per_day',
                          'social_media_hours_per_day', 'exam_stress_level']
        
        for col in numeric_columns:
            if col in engineered_data.columns:
                engineered_data[col] = pd.to_numeric(engineered_data[col], errors='coerce')
        
        # Calculate study efficiency (performance per hour studied)
        engineered_data['study_efficiency'] = (
            engineered_data['last_exam_score_percent'] / 
            (engineered_data['study_hours_per_day'] + 1)  # +1 to avoid division by zero
        )
        
        # Calculate lifestyle balance (sleep vs social media)
        engineered_data['lifestyle_balance'] = (
            engineered_data['sleep_hours_per_day'] - 
            engineered_data['social_media_hours_per_day']
        )
        
        # Encode revision frequency to numerical score
        revision_mapping = {
            'daily': 7,
            'every_other_day': 5,
            'weekly': 3,
            'biweekly': 2,
            'monthly': 1,
            'rarely': 0
        }
        
        if 'revision_frequency' in engineered_data.columns:
            engineered_data['revision_frequency_score'] = engineered_data['revision_frequency'].map(revision_mapping).fillna(3)
        else:
            engineered_data['revision_frequency_score'] = 3  # Default to weekly
        
        # Select only the features needed for prediction and ensure they're numeric
        feature_columns = [col for col in self.feature_names if col in engineered_data.columns]
        result = engineered_data[feature_columns].copy()
        
        # Ensure all columns are numeric
        for col in result.columns:
            result[col] = pd.to_numeric(result[col], errors='coerce')
        
        # Fill any NaN values with column means
        result = result.fillna(result.mean())
        
        return result
    
    def train(self, X, y, test_size=0.2):
        """
        Train the knowledge mastery model.
        
        Args:
            X (pd.DataFrame): Feature data
            y (pd.Series or np.array): Target mastery scores (0-100)
            test_size (float): Proportion of data for testing
            
        Returns:
            dict: Training metrics
        """
        # Engineer features
        if isinstance(X, pd.DataFrame):
            X_engineered = self.engineer_features(X)
        else:
            X_engineered = X
        
        # Split the data
        X_train, X_test, y_train, y_test = train_test_split(
            X_engineered, y, test_size=test_size, random_state=self.random_state
        )
        
        # Scale features for neural networks
        if self.model_type == 'neural_network':
            X_train_scaled = self.scaler.fit_transform(X_train)
            X_test_scaled = self.scaler.transform(X_test)
            
            # Train the model
            self.model.fit(X_train_scaled, y_train)
            y_pred = self.model.predict(X_test_scaled)
        else:
            # Train the model (tree-based models don't need scaling)
            self.model.fit(X_train, y_train)
            y_pred = self.model.predict(X_test)
        
        # Calculate metrics
        metrics = {
            'mse': mean_squared_error(y_test, y_pred),
            'rmse': np.sqrt(mean_squared_error(y_test, y_pred)),
            'mae': mean_absolute_error(y_test, y_pred),
            'r2_score': r2_score(y_test, y_pred)
        }
        
        self.is_trained = True
        
        # Print training results
        print("Knowledge Mastery Model Training Results:")
        print(f"RMSE: {metrics['rmse']:.4f}")
        print(f"MAE: {metrics['mae']:.4f}")
        print(f"R² Score: {metrics['r2_score']:.4f}")
        
        return metrics
    
    def predict(self, X):
        """
        Predict mastery scores for given student data.
        
        Args:
            X (pd.DataFrame): Student feature data
            
        Returns:
            np.array: Predicted mastery scores (0-100)
        """
        if not self.is_trained:
            raise ValueError("Model must be trained before making predictions. Call train() first.")
        
        # Engineer features
        if isinstance(X, pd.DataFrame):
            X_engineered = self.engineer_features(X)
        else:
            X_engineered = X
        
        # Make predictions
        if self.model_type == 'neural_network':
            X_scaled = self.scaler.transform(X_engineered)
            predictions = self.model.predict(X_scaled)
        else:
            predictions = self.model.predict(X_engineered)
        
        # Ensure predictions are within valid range (0-100)
        predictions = np.clip(predictions, 0, 100)
        
        return predictions
    
    def predict_detailed(self, X):
        """
        Predict detailed mastery scores with confidence intervals.
        
        Args:
            X (pd.DataFrame): Student feature data
            
        Returns:
            dict: Detailed predictions with confidence scores
        """
        predictions = self.predict(X)
        
        # Calculate confidence based on model type
        if hasattr(self.model, 'predict_proba'):
            # For models that support probability predictions
            confidence = np.max(self.model.predict_proba(X), axis=1)
        else:
            # For regression models, use a simple confidence calculation
            confidence = np.ones(len(predictions)) * 0.8  # Default confidence
        
        # Create detailed results
        results = []
        for i, (pred, conf) in enumerate(zip(predictions, confidence)):
            result = {
                'overall_mastery': pred,
                'confidence': conf,
                'performance_level': self._categorize_mastery(pred),
                'recommendations': self._generate_recommendations(pred)
            }
            results.append(result)
        
        return results
    
    def _categorize_mastery(self, score):
        """Categorize mastery score into performance levels."""
        if score >= 85:
            return 'Excellent'
        elif score >= 70:
            return 'Good'
        elif score >= 60:
            return 'Average'
        elif score >= 50:
            return 'Below Average'
        else:
            return 'Needs Improvement'
    
    def _generate_recommendations(self, score):
        """Generate study recommendations based on mastery score."""
        if score >= 85:
            return "Maintain current study habits. Consider advanced topics."
        elif score >= 70:
            return "Good progress. Focus on weak areas for improvement."
        elif score >= 60:
            return "Increase study time and focus on fundamentals."
        elif score >= 50:
            return "Significant improvement needed. Consider additional help."
        else:
            return "Intensive study required. Seek tutoring or extra support."
    
    def get_feature_importance(self):
        """Get feature importance for tree-based models."""
        if not self.is_trained:
            raise ValueError("Model must be trained first.")
        
        if hasattr(self.model, 'feature_importances_'):
            importance_dict = dict(zip(self.feature_names, self.model.feature_importances_))
            return sorted(importance_dict.items(), key=lambda x: x[1], reverse=True)
        else:
            return "Feature importance not available for this model type."
    
    def save_model(self, filepath):
        """Save the trained model to disk."""
        if not self.is_trained:
            raise ValueError("Cannot save untrained model.")
        
        model_data = {
            'model': self.model,
            'scaler': self.scaler,
            'model_type': self.model_type,
            'feature_names': self.feature_names,
            'is_trained': self.is_trained
        }
        joblib.dump(model_data, filepath)
        print(f"Model saved to {filepath}")
    
    @classmethod
    def load(cls, filepath):
        """Load a trained model from disk."""
        model_data = joblib.load(filepath)
        
        # Create new instance
        instance = cls(model_type=model_data['model_type'])
        instance.model = model_data['model']
        instance.scaler = model_data['scaler']
        instance.feature_names = model_data['feature_names']
        instance.is_trained = model_data['is_trained']
        
        return instance