#!/usr/bin/env python3
import sys
import json

print("Test script started", file=sys.stderr)

if len(sys.argv) < 2:
    result = {
        'success': False,
        'error': 'No input data provided',
        'exam_score': 65
    }
else:
    try:
        data = json.loads(sys.argv[1])
        result = {
            'success': True,
            'exam_score': 75.5,
            'performance_category': 'Good Performance',
            'insights': ['Test insight'],
            'recommendations': [{'category': 'Test', 'priority': 'High', 'recommendation': 'Test rec', 'impact': 'Test impact'}]
        }
    except Exception as e:
        result = {
            'success': False,
            'error': str(e),
            'exam_score': 65
        }

print(json.dumps(result, indent=2))
