#!/usr/bin/env python3
"""
Test script for Knowledge Mastery Layer implementation.
"""

import sys
import os
import pandas as pd
import numpy as np

# Add the src directory to the path
sys.path.append(os.path.join(os.path.dirname(__file__), 'src'))

from src.models.knowledge_mastery import KnowledgeMasteryModel

def create_sample_data():
    """Create sample student data for testing."""
    np.random.seed(42)
    
    # Generate sample data for 100 students
    n_students = 100
    
    data = {
        'last_exam_score_percent': np.random.normal(75, 15, n_students),
        'study_hours_per_day': np.random.uniform(1, 10, n_students),
        'sleep_hours_per_day': np.random.uniform(6, 10, n_students),
        'social_media_hours_per_day': np.random.uniform(0, 5, n_students),
        'exam_stress_level': np.random.randint(1, 11, n_students),
        'revision_frequency': np.random.choice(['daily', 'weekly', 'monthly'], n_students)
    }
    
    df = pd.DataFrame(data)
    
    # Ensure scores are within valid range
    df['last_exam_score_percent'] = np.clip(df['last_exam_score_percent'], 0, 100)
    
    # Create target variable (future mastery score based on current performance + some noise)
    target = (
        df['last_exam_score_percent'] * 0.6 +
        df['study_hours_per_day'] * 3 +
        (10 - df['exam_stress_level']) * 2 +
        np.random.normal(0, 5, n_students)
    )
    target = np.clip(target, 0, 100)
    
    return df, target

def test_knowledge_mastery_models():
    """Test all available Knowledge Mastery models."""
    print("=" * 60)
    print("TESTING KNOWLEDGE MASTERY LAYER")
    print("=" * 60)
    
    # Create sample data
    X, y = create_sample_data()
    print(f"Created sample data: {len(X)} students")
    print(f"Features: {list(X.columns)}")
    print(f"Target range: {y.min():.2f} - {y.max():.2f}")
    print()
    
    # Test different model types
    model_types = ['xgboost', 'lightgbm', 'gradient_boosting', 'neural_network']
    
    for model_type in model_types:
        print(f"Testing {model_type.upper()} Model:")
        print("-" * 40)
        
        try:
            # Initialize model
            model = KnowledgeMasteryModel(model_type=model_type)
            
            # Train model
            metrics = model.train(X, y)
            
            # Make predictions on a small sample
            sample_X = X.head(5)
            predictions = model.predict(sample_X)
            
            print(f"Sample predictions: {predictions}")
            
            # Get detailed predictions
            detailed_predictions = model.predict_detailed(sample_X)
            print("Detailed prediction for first student:")
            print(f"  Mastery Score: {detailed_predictions[0]['overall_mastery']:.2f}")
            print(f"  Performance Level: {detailed_predictions[0]['performance_level']}")
            print(f"  Recommendation: {detailed_predictions[0]['recommendations']}")
            
            # Get feature importance (if available)
            try:
                importance = model.get_feature_importance()
                print("Feature Importance:")
                for feature, imp in importance[:3]:  # Top 3 features
                    print(f"  {feature}: {imp:.4f}")
            except:
                print("Feature importance not available for this model")
            
            print("✅ Model tested successfully!")
            
        except Exception as e:
            print(f"❌ Error testing {model_type}: {str(e)}")
        
        print()

def test_model_persistence():
    """Test saving and loading models."""
    print("Testing Model Persistence:")
    print("-" * 30)
    
    # Create and train a model
    X, y = create_sample_data()
    model = KnowledgeMasteryModel(model_type='xgboost')
    model.train(X, y)
    
    # Save model
    model_path = "test_knowledge_mastery_model.pkl"
    model.save_model(model_path)
    
    # Load model
    loaded_model = KnowledgeMasteryModel.load(model_path)
    
    # Test predictions match
    original_pred = model.predict(X.head(3))
    loaded_pred = loaded_model.predict(X.head(3))
    
    if np.allclose(original_pred, loaded_pred):
        print("✅ Model save/load successful!")
    else:
        print("❌ Model predictions don't match after loading")
    
    # Clean up
    import os
    if os.path.exists(model_path):
        os.remove(model_path)

def main():
    """Main test function."""
    try:
        test_knowledge_mastery_models()
        test_model_persistence()
        
        print("=" * 60)
        print("🎉 ALL KNOWLEDGE MASTERY TESTS COMPLETED!")
        print("=" * 60)
        
    except Exception as e:
        print(f"❌ Test failed: {str(e)}")
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    main()
