#!/usr/bin/env python3
"""
Questionnaire-based Study Plan Prediction Script
This script takes questionnaire data and predicts exam scores using ML models.
"""

import sys
import json
import numpy as np
import pandas as pd
import os
from pathlib import Path

# Add the src directory to Python path
current_dir = Path(__file__).parent
src_dir = current_dir / 'src'
sys.path.append(str(src_dir))

try:
    from models.user_profiling import UserProfilingModel
    from models.recommendation_engine import StudyPlanRecommendationEngine
    from utils.logger import setup_logger
except ImportError:
    # Fallback imports
    pass

class QuestionnairePredictor:
    def __init__(self):
        self.feature_mappings = {
            'gender': {'Male': 0, 'Female': 1, 'Prefer not to say': 2},
            'revision_frequency': {'Daily': 3, 'Weekly': 2, 'Before exams': 1, 'Rarely': 0},
            'preferred_study_time': {'Morning': 0, 'Afternoon': 1, 'Evening': 2, 'Night': 3},
            'uses_online_learning': {'Yes': 1, 'No': 0}
        }
        
    def encode_features(self, data):
        """Convert categorical features to numerical."""
        encoded = data.copy()
        
        for feature, mapping in self.feature_mappings.items():
            if feature in encoded:
                encoded[feature] = mapping.get(encoded[feature], 0)
                
        return encoded
    
    def predict_exam_score(self, data):
        """Predict exam score using rule-based model."""
        # Encode categorical features
        encoded_data = self.encode_features(data)
        
        # Base score calculation
        base_score = 50
        
        # Age factor (optimal learning age)
        age = encoded_data['age']
        if 19 <= age <= 23:
            base_score += 5
        elif age < 18 or age > 26:
            base_score -= 3
            
        # Study hours impact (major factor)
        study_hours = encoded_data['study_hours_per_day']
        if study_hours >= 8:
            base_score += 20
        elif study_hours >= 6:
            base_score += 15
        elif study_hours >= 4:
            base_score += 10
        elif study_hours >= 2:
            base_score += 5
        else:
            base_score -= 5
            
        # Revision frequency impact
        revision_score = encoded_data['revision_frequency']
        base_score += revision_score * 5
        
        # Sleep impact
        sleep_hours = encoded_data['sleep_hours_per_day']
        if 7 <= sleep_hours <= 9:
            base_score += 8
        elif 6 <= sleep_hours <= 10:
            base_score += 3
        elif sleep_hours < 6:
            base_score -= 8
        else:  # > 10
            base_score -= 5
            
        # Social media impact (negative correlation)
        social_media = encoded_data['social_media_hours_per_day']
        if social_media <= 1:
            base_score += 5
        elif social_media <= 3:
            base_score += 2
        elif social_media >= 6:
            base_score -= 10
        else:
            base_score -= 5
            
        # Stress level impact
        stress = encoded_data['exam_stress_level']
        if stress <= 2:
            base_score += 3
        elif stress >= 4:
            base_score -= 5
            
        # Online learning impact
        online_learning = encoded_data['uses_online_learning']
        base_score += online_learning * 3
        
        # Preferred study time impact
        study_time = encoded_data['preferred_study_time']
        if study_time == 0:  # Morning
            base_score += 3
        elif study_time == 2:  # Evening
            base_score += 2
            
        # Ensure score is within reasonable bounds
        final_score = max(35, min(95, base_score))
        
        return round(final_score, 1)
    
    def generate_performance_insights(self, data, predicted_score):
        """Generate performance insights based on prediction."""
        insights = []
        
        # Score-based insights
        if predicted_score >= 85:
            insights.append("Excellent study habits indicate high academic performance")
        elif predicted_score >= 75:
            insights.append("Good study patterns with room for optimization")
        elif predicted_score >= 65:
            insights.append("Average performance with significant improvement potential")
        else:
            insights.append("Study habits need substantial improvement for better results")
            
        # Specific factor insights
        if data['study_hours_per_day'] < 4:
            insights.append(f"Current {data['study_hours_per_day']} daily study hours may be insufficient")
        elif data['study_hours_per_day'] >= 7:
            insights.append("Excellent study time dedication")
            
        if data['revision_frequency'] == 'Rarely':
            insights.append("Infrequent revision significantly impacts retention")
        elif data['revision_frequency'] == 'Daily':
            insights.append("Daily revision habit strongly supports learning")
            
        if data['social_media_hours_per_day'] > 4:
            insights.append(f"High social media usage ({data['social_media_hours_per_day']} hours) may reduce focus")
            
        if data['sleep_hours_per_day'] < 6:
            insights.append("Insufficient sleep negatively affects cognitive performance")
        elif 7 <= data['sleep_hours_per_day'] <= 9:
            insights.append("Optimal sleep duration supports learning")
            
        if data['exam_stress_level'] >= 4:
            insights.append("High stress levels may impact exam performance")
            
        return insights
    
    def generate_recommendations(self, data, predicted_score):
        """Generate specific recommendations."""
        recommendations = []
        
        # Study time recommendations
        if data['study_hours_per_day'] < 4:
            recommendations.append({
                'category': 'Study Duration',
                'priority': 'High',
                'recommendation': 'Increase daily study time to 4-6 hours',
                'impact': 'Could improve score by 10-15 points'
            })
        elif data['study_hours_per_day'] > 8:
            recommendations.append({
                'category': 'Study Duration',
                'priority': 'Medium',
                'recommendation': 'Ensure quality over quantity - breaks are important',
                'impact': 'Prevents burnout and maintains efficiency'
            })
            
        # Revision recommendations
        if data['revision_frequency'] in ['Rarely', 'Before exams']:
            recommendations.append({
                'category': 'Revision Strategy',
                'priority': 'High',
                'recommendation': 'Implement regular revision - at least weekly',
                'impact': 'Regular revision can boost retention by 25-30%'
            })
            
        # Sleep recommendations
        if data['sleep_hours_per_day'] < 7 or data['sleep_hours_per_day'] > 9:
            recommendations.append({
                'category': 'Sleep Optimization',
                'priority': 'Medium',
                'recommendation': 'Maintain 7-9 hours of sleep for optimal brain function',
                'impact': 'Improves memory consolidation and focus'
            })
            
        # Social media recommendations
        if data['social_media_hours_per_day'] > 3:
            recommendations.append({
                'category': 'Digital Wellness',
                'priority': 'High',
                'recommendation': 'Limit social media to 1-2 hours during study periods',
                'impact': 'Reduces distractions and improves concentration'
            })
            
        # Stress management
        if data['exam_stress_level'] >= 4:
            recommendations.append({
                'category': 'Stress Management',
                'priority': 'High',
                'recommendation': 'Practice stress reduction techniques (meditation, exercise)',
                'impact': 'Lower stress improves cognitive performance and recall'
            })
            
        # Study time optimization
        if data['preferred_study_time'] == 'Night':
            recommendations.append({
                'category': 'Study Timing',
                'priority': 'Low',
                'recommendation': 'Consider morning study sessions for complex topics',
                'impact': 'Morning hours often provide better focus and retention'
            })
            
        return recommendations
    
    def predict(self, questionnaire_data):
        """Main prediction function."""
        try:
            # Predict exam score
            predicted_score = self.predict_exam_score(questionnaire_data)
            
            # Generate insights and recommendations
            insights = self.generate_performance_insights(questionnaire_data, predicted_score)
            recommendations = self.generate_recommendations(questionnaire_data, predicted_score)
            
            # Performance category
            if predicted_score >= 85:
                category = "Excellent Performance"
            elif predicted_score >= 75:
                category = "Good Performance"
            elif predicted_score >= 65:
                category = "Average Performance"
            else:
                category = "Needs Improvement"
            
            return {
                'success': True,
                'exam_score': predicted_score,
                'performance_category': category,
                'insights': insights,
                'recommendations': recommendations,
                'study_pattern_analysis': {
                    'daily_study_hours': questionnaire_data['study_hours_per_day'],
                    'revision_habit': questionnaire_data['revision_frequency'],
                    'preferred_time': questionnaire_data['preferred_study_time'],
                    'digital_usage': questionnaire_data['social_media_hours_per_day'],
                    'sleep_quality': questionnaire_data['sleep_hours_per_day'],
                    'stress_level': questionnaire_data['exam_stress_level']
                }
            }
            
        except Exception as e:
            return {
                'success': False,
                'error': str(e),
                'exam_score': 65  # Default fallback score
            }

def main():
    """Main function called from Laravel."""
    try:
        # Debug: Print to stderr for debugging
        import sys
        print("Script started", file=sys.stderr)
        
        # Get input data from command line argument
        if len(sys.argv) < 2:
            print("No input data provided", file=sys.stderr)
            raise ValueError("No input data provided")
            
        input_arg = sys.argv[1]
        
        # Check if input is a file (starts with @)
        if input_arg.startswith('@'):
            file_path = input_arg[1:]  # Remove @ prefix
            with open(file_path, 'r') as f:
                input_data = f.read()
            print(f"Data read from file: {file_path}", file=sys.stderr)
        else:
            input_data = input_arg
            print(f"Input data received directly: {input_data}", file=sys.stderr)
        
        questionnaire_data = json.loads(input_data)
        
        # Validate required fields
        required_fields = [
            'age', 'gender', 'study_hours_per_day', 'revision_frequency',
            'preferred_study_time', 'uses_online_learning', 'social_media_hours_per_day',
            'sleep_hours_per_day', 'exam_stress_level'
        ]
        
        for field in required_fields:
            if field not in questionnaire_data:
                raise ValueError(f"Missing required field: {field}")
        
        print("Validation passed", file=sys.stderr)
        
        # Create predictor and generate prediction
        predictor = QuestionnairePredictor()
        result = predictor.predict(questionnaire_data)
        
        print("Prediction generated", file=sys.stderr)
        
        # Output result as JSON
        print(json.dumps(result, indent=2))
        
    except Exception as e:
        # Output error as JSON
        print(f"Error occurred: {str(e)}", file=sys.stderr)
        error_result = {
            'success': False,
            'error': str(e),
            'exam_score': 65
        }
        print(json.dumps(error_result, indent=2))

if __name__ == '__main__':
    main()
