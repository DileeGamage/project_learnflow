#!/usr/bin/env python3
"""
Minimal test version of the study plan generator
"""

import sys
import os
import json
from datetime import datetime

def generate_simple_study_plan(student_data):
    """Generate a simple study plan without complex dependencies"""
    
    # Basic profile analysis
    age = student_data.get('age', 20)
    study_hours = student_data.get('study_hours_per_day', 4)
    preferred_time = student_data.get('preferred_study_time', 'Afternoon')
    stress_level = student_data.get('exam_stress_level', 3)
    exam_score = student_data.get('last_exam_score_percent', 75)
    
    # Determine user type
    if exam_score >= 85:
        performance = "high_performer"
    elif exam_score >= 70:
        performance = "average_performer"  
    else:
        performance = "needs_improvement"
        
    if study_hours >= 6:
        intensity = "intensive"
    elif study_hours >= 4:
        intensity = "moderate"
    else:
        intensity = "light"
    
    # Create study plan
    result = {
        'user_id': student_data.get('user_id', 'unknown'),
        'generated_at': datetime.now().isoformat(),
        'duration_days': 21,
        'user_profile': {
            'user_type': f"{performance}_{intensity}_learner",
            'optimal_study_time': preferred_time,
            'study_intensity': intensity.title(),
            'consistency_level': 'Medium' if stress_level <= 3 else 'High'
        },
        'recommendations': [
            f"Focus your study sessions during {preferred_time.lower()} hours for optimal performance",
            f"With {study_hours} hours daily, maintain consistent study blocks",
            "Take 15-minute breaks every hour to maintain concentration",
            "Review material before sleep to improve retention",
            "Practice active recall techniques for better learning"
        ],
        'focus_areas': [
            "Mathematics: Algebra and problem-solving techniques",
            "Science: Core concepts and practical applications", 
            "Language: Reading comprehension and writing skills",
            "General: Time management and study organization"
        ],
        'daily_schedule': {
            f"{preferred_time} (Primary)": "Core subject study - 2 hours",
            "Evening Review": "Quick revision - 30 minutes", 
            "Break Time": "Rest and relaxation - 15 minutes every hour"
        },
        'weekly_plan': {
            'Monday': f"{preferred_time}: Mathematics focus (2hrs), Evening: Review (30min)",
            'Tuesday': f"{preferred_time}: Science concepts (2hrs), Evening: Practice (30min)",
            'Wednesday': f"{preferred_time}: Language skills (2hrs), Evening: Reading (30min)", 
            'Thursday': f"{preferred_time}: Problem solving (2hrs), Evening: Review (30min)",
            'Friday': f"{preferred_time}: Mixed subjects (2hrs), Evening: Assessment (30min)",
            'Saturday': "Light review and practice tests",
            'Sunday': "Rest day with optional light reading"
        },
        'success': True
    }
    
    return result

def main():
    """Main function for command line execution"""
    try:
        if len(sys.argv) != 2:
            error_output = {
                'error': True,
                'message': 'Usage: python generate_study_plan_simple.py "JSON_DATA"',
                'fallback': True,
                'success': False
            }
            print(json.dumps(error_output))
            sys.exit(1)
        
        # Parse JSON input
        json_input = sys.argv[1]
        student_data = json.loads(json_input)
        
        # Generate study plan
        result = generate_simple_study_plan(student_data)
        
        # Output JSON
        print(json.dumps(result, default=str))
        
    except json.JSONDecodeError as e:
        error_output = {
            'error': True,
            'message': f'Invalid JSON input: {str(e)}',
            'fallback': True,
            'success': False
        }
        print(json.dumps(error_output))
        sys.exit(1)
        
    except Exception as e:
        error_output = {
            'error': True,
            'message': f'Error generating study plan: {str(e)}',
            'fallback': True,
            'success': False
        }
        print(json.dumps(error_output))
        sys.exit(1)

if __name__ == '__main__':
    main()
