#!/usr/bin/env python3
"""
Simplified 3-Layer ML System Demonstration
Shows the core functionality of all three layers:
1. User Profiling Layer (Random Forest Classifier)
2. Knowledge Mastery Layer (XGBoost Regressor) 
3. Recommendation Layer (Basic Implementation)
"""

import sys
import os
import pandas as pd
import numpy as np

# Add the src directory to the path
sys.path.append(os.path.join(os.path.dirname(__file__), 'src'))

from src.models.user_profiling import UserProfilingModel
from src.models.knowledge_mastery import KnowledgeMasteryModel

def create_sample_student_data():
    """Create sample data for demonstration students."""
    students_data = [
        {
            'name': 'Alice Johnson',
            'age': 20,
            'study_hours_per_day': 6,
            'sleep_hours_per_day': 8,
            'social_media_hours_per_day': 2,
            'exam_stress_level': 7,
            'last_exam_score_percent': 85,
            'revision_frequency': 'daily'
        },
        {
            'name': 'Bob Smith', 
            'age': 19,
            'study_hours_per_day': 3,
            'sleep_hours_per_day': 6,
            'social_media_hours_per_day': 4,
            'exam_stress_level': 9,
            'last_exam_score_percent': 65,
            'revision_frequency': 'weekly'
        },
        {
            'name': 'Carol Davis',
            'age': 21,
            'study_hours_per_day': 8,
            'sleep_hours_per_day': 7,
            'social_media_hours_per_day': 1,
            'exam_stress_level': 4,
            'last_exam_score_percent': 92,
            'revision_frequency': 'daily'
        }
    ]
    
    return pd.DataFrame(students_data)

def train_models():
    """Train the User Profiling and Knowledge Mastery models."""
    print("🚀 TRAINING MODELS...")
    print("=" * 50)
    
    # Create training dataset
    np.random.seed(42)
    n_students = 500
    
    # Generate training data
    training_data = {
        'age': np.random.randint(18, 25, n_students),
        'study_hours_per_day': np.random.uniform(1, 10, n_students),
        'sleep_hours_per_day': np.random.uniform(6, 10, n_students),
        'social_media_hours_per_day': np.random.uniform(0, 6, n_students),
        'exam_stress_level': np.random.randint(1, 11, n_students),
        'last_exam_score_percent': np.random.normal(75, 15, n_students),
        'revision_frequency': np.random.choice(['daily', 'weekly', 'monthly'], n_students)
    }
    
    df = pd.DataFrame(training_data)
    df['last_exam_score_percent'] = np.clip(df['last_exam_score_percent'], 0, 100)
    
    # Create labels for user profiling
    def assign_learning_type(row):
        if row['study_hours_per_day'] > 6:
            return 'intensive_studier'
        elif row['study_hours_per_day'] > 3:
            return 'moderate_studier'
        else:
            return 'light_studier'
    
    df['learning_type'] = df.apply(assign_learning_type, axis=1)
    
    # Create target for knowledge mastery
    mastery_target = (
        df['last_exam_score_percent'] * 0.6 +
        df['study_hours_per_day'] * 3 +
        (10 - df['exam_stress_level']) * 2 +
        np.random.normal(0, 5, n_students)
    )
    df['future_mastery_score'] = np.clip(mastery_target, 0, 100)
    
    # 1. Train User Profiling Model (Random Forest)
    print("1️⃣ Training User Profiling Model (Random Forest Classifier)...")
    user_profiling_features = ['age', 'study_hours_per_day', 'sleep_hours_per_day', 
                              'social_media_hours_per_day', 'exam_stress_level', 'last_exam_score_percent']
    
    user_model = UserProfilingModel(model_type='random_forest')
    user_model.train(df[user_profiling_features], df['learning_type'])
    print("✅ User Profiling Model trained!")
    
    # 2. Train Knowledge Mastery Model (XGBoost)
    print("\n2️⃣ Training Knowledge Mastery Model (XGBoost Regressor)...")
    mastery_features = ['last_exam_score_percent', 'study_hours_per_day', 'sleep_hours_per_day',
                       'social_media_hours_per_day', 'exam_stress_level', 'revision_frequency']
    
    mastery_model = KnowledgeMasteryModel(model_type='xgboost')
    mastery_model.train(df[mastery_features], df['future_mastery_score'])
    print("✅ Knowledge Mastery Model trained!")
    
    return user_model, mastery_model

def generate_basic_recommendations(learning_type, mastery_score, performance_level):
    """Generate basic study recommendations based on profiling and mastery."""
    recommendations = []
    
    # Based on learning type
    if learning_type == 'intensive_studier':
        recommendations.append("• Continue intensive study habits")
        recommendations.append("• Focus on advanced topics")
        recommendations.append("• Consider peer tutoring opportunities")
    elif learning_type == 'moderate_studier':
        recommendations.append("• Maintain balanced study schedule")
        recommendations.append("• Gradually increase study time if possible")
        recommendations.append("• Use active learning techniques")
    else:  # light_studier
        recommendations.append("• Increase daily study time gradually")
        recommendations.append("• Focus on foundational concepts first")
        recommendations.append("• Use short, frequent study sessions")
    
    # Based on mastery score
    if mastery_score >= 85:
        recommendations.append("• Explore challenging practice problems")
        recommendations.append("• Consider advanced coursework")
    elif mastery_score >= 70:
        recommendations.append("• Review weak areas identified in assessments")
        recommendations.append("• Practice application-based problems")
    else:
        recommendations.append("• Focus on fundamental concepts")
        recommendations.append("• Seek additional help or tutoring")
        recommendations.append("• Create a structured review schedule")
    
    return recommendations

def demonstrate_system():
    """Demonstrate the complete system functionality."""
    print("\n" + "=" * 70)
    print("🎓 3-LAYER ML STUDY PLAN SYSTEM DEMONSTRATION")
    print("=" * 70)
    
    # Train models
    user_model, mastery_model = train_models()
    
    # Get sample student data
    students = create_sample_student_data()
    
    print("\n" + "=" * 70)
    print("📊 ANALYZING STUDENTS WITH 3-LAYER SYSTEM")
    print("=" * 70)
    
    for i, student in students.iterrows():
        print(f"\n🧑‍🎓 Student: {student['name']}")
        print("-" * 40)
        print(f"📋 Profile: {student['age']}yo, {student['study_hours_per_day']}h study/day, "
              f"{student['last_exam_score_percent']}% last exam")
        
        # Prepare data for predictions
        user_features = ['age', 'study_hours_per_day', 'sleep_hours_per_day', 
                        'social_media_hours_per_day', 'exam_stress_level', 'last_exam_score_percent']
        mastery_features = ['last_exam_score_percent', 'study_hours_per_day', 'sleep_hours_per_day',
                           'social_media_hours_per_day', 'exam_stress_level', 'revision_frequency']
        
        student_user_data = student[user_features].values.reshape(1, -1)
        student_mastery_data = student[mastery_features].to_frame().T
        
        # LAYER 1: User Profiling Prediction (Random Forest)
        learning_type = user_model.predict(student_user_data)[0]
        print(f"\n1️⃣ USER PROFILING (Random Forest Classifier)")
        print(f"   📈 Learning Type: {learning_type}")
        
        # LAYER 2: Knowledge Mastery Prediction (XGBoost)
        mastery_prediction = mastery_model.predict_detailed(student_mastery_data)[0]
        mastery_score = mastery_prediction['overall_mastery']
        performance_level = mastery_prediction['performance_level']
        
        print(f"\n2️⃣ KNOWLEDGE MASTERY (XGBoost Regressor)")
        print(f"   🎯 Predicted Mastery Score: {mastery_score:.1f}/100")
        print(f"   📊 Performance Level: {performance_level}")
        
        # LAYER 3: Basic Recommendation System
        recommendations = generate_basic_recommendations(learning_type, mastery_score, performance_level)
        
        print(f"\n3️⃣ STUDY RECOMMENDATIONS (Rule-Based + ML Insights)")
        for rec in recommendations:
            print(f"   {rec}")
        
        print()

def show_system_architecture():
    """Display the complete system architecture."""
    print("\n" + "=" * 70)
    print("🏗️ COMPLETE 3-LAYER ML ARCHITECTURE")
    print("=" * 70)
    
    print("""
┌─────────────────────────────────────────────────────────────────┐
│                    3-LAYER ML SYSTEM                            │
└─────────────────────────────────────────────────────────────────┘

1️⃣ USER PROFILING LAYER
   ✅ Algorithm: Random Forest Classifier
   ✅ Input: Student demographics, study habits, performance data
   ✅ Output: Learning behavior classification
   ✅ Examples: intensive_studier, moderate_studier, light_studier

2️⃣ KNOWLEDGE MASTERY LAYER  
   ✅ Primary: XGBoost Regressor
   ✅ Alternatives: LightGBM, Gradient Boosting, Neural Networks
   ✅ Input: Study patterns, efficiency metrics, lifestyle balance
   ✅ Output: Performance prediction (0-100) + confidence levels

3️⃣ RECOMMENDATION LAYER
   ✅ Hybrid Approach:
       • K-Means Clustering (groups similar learners)
       • NMF Collaborative Filtering (content similarity)
       • Rule-Based System (educational best practices)
   ✅ Input: User profile + Mastery predictions
   ✅ Output: Personalized study plans and recommendations

🔄 INTEGRATION: All layers feed into each other for comprehensive
   personalized educational recommendations.
    """)

def main():
    """Main demonstration function."""
    try:
        demonstrate_system()
        show_system_architecture()
        
        print("\n" + "=" * 70)
        print("✅ 3-LAYER ML SYSTEM SUCCESSFULLY DEMONSTRATED!")
        print("🎯 User Profiling: Random Forest Classifier")
        print("🎯 Knowledge Mastery: XGBoost Regressor") 
        print("🎯 Recommendations: Hybrid ML Approach")
        print("=" * 70)
        
    except Exception as e:
        print(f"❌ Error: {str(e)}")
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    main()
