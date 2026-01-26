import sys
import os
sys.path.append(os.path.join(os.path.dirname(__file__), '..'))

from models.knowledge_mastery import KnowledgeMasteryModel
from utils.metrics_logger import log_metrics
import pandas as pd
import numpy as np

def load_data(file_path):
    """Load data from CSV file or create sample data if file doesn't exist."""
    try:
        data = pd.read_csv(file_path)
        return data
    except FileNotFoundError:
        print(f"File {file_path} not found. Creating sample data...")
        return create_sample_data()

def create_sample_data():
    """Create sample student data for training."""
    np.random.seed(42)
    n_students = 500
    
    data = {
        'last_exam_score_percent': np.random.normal(75, 15, n_students),
        'study_hours_per_day': np.random.uniform(1, 10, n_students),
        'sleep_hours_per_day': np.random.uniform(6, 10, n_students),
        'social_media_hours_per_day': np.random.uniform(0, 5, n_students),
        'exam_stress_level': np.random.randint(1, 11, n_students),
        'revision_frequency': np.random.choice(['daily', 'weekly', 'monthly'], n_students)
    }
    
    df = pd.DataFrame(data)
    df['last_exam_score_percent'] = np.clip(df['last_exam_score_percent'], 0, 100)
    
    # Create target variable (future mastery score)
    target = (
        df['last_exam_score_percent'] * 0.6 +
        df['study_hours_per_day'] * 3 +
        (10 - df['exam_stress_level']) * 2 +
        np.random.normal(0, 5, n_students)
    )
    df['target_mastery_score'] = np.clip(target, 0, 100)
    
    return df

def train_knowledge_mastery_model(data, model_type='xgboost'):
    """
    Train the Knowledge Mastery Model using the new implementation.
    
    Args:
        data (pd.DataFrame): Training data
        model_type (str): Type of model to train ('xgboost', 'lightgbm', 'gradient_boosting', 'neural_network')
    
    Returns:
        KnowledgeMasteryModel: Trained model
    """
    print(f"Training Knowledge Mastery Model with {model_type.upper()}...")
    
    # Prepare features and target
    feature_columns = [
        'last_exam_score_percent', 'study_hours_per_day', 'sleep_hours_per_day',
        'social_media_hours_per_day', 'exam_stress_level', 'revision_frequency'
    ]
    
    X = data[feature_columns]
    y = data['target_mastery_score']
    
    # Initialize and train model
    model = KnowledgeMasteryModel(model_type=model_type)
    metrics = model.train(X, y)
    
    # Add training sample counts to metrics
    metrics['training_samples'] = len(X)
    metrics['test_samples'] = int(len(X) * 0.2)
    metrics['test_size_ratio'] = 0.2
    
    # Get feature importance if available
    feature_importance = None
    try:
        importance = model.get_feature_importance()
        feature_importance = importance
        print("\nFeature Importance:")
        for feature, imp in importance:
            print(f"  {feature}: {imp:.4f}")
    except:
        pass
    
    # Get hyperparameters
    hyperparameters = {}
    if hasattr(model.model, 'get_params'):
        hyperparameters = model.model.get_params()
    
    # Save metrics to database
    try:
        log_metrics(
            model_name='knowledge_mastery',
            model_type=model_type,
            metrics=metrics,
            feature_importance=feature_importance,
            hyperparameters=hyperparameters,
            notes=f'Trained on {len(X)} samples using {model_type}'
        )
    except Exception as e:
        print(f"Warning: Could not save metrics to database: {e}")
    
    return model

def main():
    """Main training function for Knowledge Mastery Models."""
    print("=" * 60)
    print("TRAINING KNOWLEDGE MASTERY MODELS")
    print("=" * 60)
    
    # Load or create training data
    data = load_data('data/processed/knowledge_mastery_data.csv')
    print(f"Loaded data: {len(data)} students")
    
    # Train different model types
    model_types = ['xgboost', 'lightgbm', 'gradient_boosting']
    trained_models = {}
    
    for model_type in model_types:
        try:
            print(f"\n{'-'*40}")
            model = train_knowledge_mastery_model(data, model_type)
            trained_models[model_type] = model
            
            # Save the model
            model_path = f'models/knowledge_mastery_{model_type}.pkl'
            os.makedirs('models', exist_ok=True)
            model.save_model(model_path)
            print(f"Model saved to {model_path}")
            
        except Exception as e:
            print(f"Error training {model_type}: {str(e)}")
    
    # Select best model (XGBoost as primary)
    if 'xgboost' in trained_models:
        best_model = trained_models['xgboost']
        best_model.save_model('models/knowledge_mastery_best.pkl')
        print(f"\n✅ Best model (XGBoost) saved as knowledge_mastery_best.pkl")
    
    print("\n" + "="*60)
    print("KNOWLEDGE MASTERY MODEL TRAINING COMPLETED!")
    print("="*60)

if __name__ == '__main__':
    main()