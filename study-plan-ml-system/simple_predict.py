#!/usr/bin/env python3
import sys
import json

def simple_predict(data):
    """Simple prediction function"""
    age = data.get('age', 20)
    study_hours = data.get('study_hours_per_day', 4)
    sleep_hours = data.get('sleep_hours_per_day', 7)
    social_media = data.get('social_media_hours_per_day', 2)
    stress = data.get('exam_stress_level', 3)
    
    # Simple scoring
    score = 50
    score += min(study_hours * 3, 20)  # Study hours bonus
    score += 10 if 7 <= sleep_hours <= 9 else 0  # Sleep bonus
    score -= social_media * 2  # Social media penalty
    score -= stress * 2  # Stress penalty
    score += 5 if data.get('revision_frequency') == 'Daily' else 0
    score += 3 if data.get('uses_online_learning') == 'Yes' else 0
    
    score = max(40, min(95, score))
    
    category = "Excellent" if score >= 85 else "Good" if score >= 75 else "Average" if score >= 65 else "Needs Improvement"
    
    return {
        'success': True,
        'exam_score': round(score, 1),
        'performance_category': f"{category} Performance",
        'insights': [
            f"Predicted exam score: {score}%",
            f"Study hours per day: {study_hours}",
            f"Sleep pattern: {sleep_hours} hours",
            "Analysis based on study habits assessment"
        ],
        'recommendations': [
            {
                'category': 'Study Optimization',
                'priority': 'High',
                'recommendation': 'Focus on consistent daily study routine',
                'impact': 'Improves retention and performance'
            },
            {
                'category': 'Time Management',
                'priority': 'Medium', 
                'recommendation': 'Balance study time with adequate rest',
                'impact': 'Enhances cognitive performance'
            }
        ]
    }

def main():
    try:
        if len(sys.argv) < 2:
            raise ValueError("No input provided")
        
        input_arg = sys.argv[1]
        
        if input_arg.startswith('@'):
            # File input
            file_path = input_arg[1:]
            with open(file_path, 'r') as f:
                data = json.load(f)
        else:
            # Direct input
            data = json.loads(input_arg)
        
        result = simple_predict(data)
        print(json.dumps(result, indent=2))
        
    except Exception as e:
        error_result = {
            'success': False,
            'error': str(e),
            'exam_score': 65
        }
        print(json.dumps(error_result, indent=2))

if __name__ == '__main__':
    main()
