#!/usr/bin/env python3

from flask import Flask, request, jsonify
from flask_cors import CORS
import logging
import traceback

# Configure logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

app = Flask(__name__)
CORS(app)

@app.route('/health', methods=['GET'])
def health():
    """Health check endpoint"""
    logger.info("Health check requested")
    return jsonify({
        'status': 'healthy',
        'service': 'Enhanced Free Quiz Generator (Basic)',
        'timestamp': '2025-09-05T09:30:00',
        'device': 'cpu'
    })

@app.route('/generate-quiz', methods=['POST'])
def generate_quiz():
    """Generate quiz endpoint - basic fallback version"""
    try:
        logger.info("Quiz generation requested")
        data = request.get_json()
        
        if not data:
            return jsonify({
                'success': False,
                'error': 'No JSON data provided'
            }), 400
        
        content = data.get('content', '')
        num_questions = data.get('num_questions', 5)
        question_types = data.get('question_types', ['multiple_choice'])
        
        if len(content.strip()) < 100:
            return jsonify({
                'success': False,
                'error': 'Content too short for quiz generation'
            })
        
        # Simple fallback quiz generation
        questions = {}
        
        if 'multiple_choice' in question_types:
            questions['multiple_choice'] = []
            for i in range(min(num_questions, 3)):
                questions['multiple_choice'].append({
                    'question': f'Question {i+1}: What is the main topic discussed in this content?',
                    'options': [
                        'A) Primary concept',
                        'B) Secondary topic', 
                        'C) Related subject',
                        'D) Supporting detail'
                    ],
                    'correct_answer': 'A',
                    'explanation': 'Based on content analysis.',
                    'difficulty': 'medium',
                    'topic': 'comprehension'
                })
        
        if 'true_false' in question_types:
            questions['true_false'] = []
            for i in range(min(num_questions//2, 2)):
                questions['true_false'].append({
                    'question': f'True or False: This content discusses important concepts.',
                    'correct_answer': 'True',
                    'explanation': 'The content contains educational material.',
                    'difficulty': 'easy',
                    'topic': 'comprehension'
                })
        
        result = {
            'success': True,
            'questions': questions,
            'total_questions': sum(len(q_list) for q_list in questions.values()),
            'estimated_time': 5,
            'difficulty_level': 'medium',
            'generated_by': 'basic_fallback_service',
            'note': 'This is a basic fallback quiz generator. Full AI features temporarily unavailable.'
        }
        
        logger.info(f"Generated quiz with {result['total_questions']} questions")
        return jsonify(result)
        
    except Exception as e:
        logger.error(f"Quiz generation error: {e}")
        logger.error(traceback.format_exc())
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

if __name__ == '__main__':
    logger.info("Starting Enhanced Free Quiz Generation Service (Basic Mode)...")
    logger.info("Service will be available at http://127.0.0.1:5002")
    
    try:
        # Use 127.0.0.1 to match Laravel configuration
        app.run(host='127.0.0.1', port=5002, debug=False, threaded=True, use_reloader=False)
    except Exception as e:
        logger.error(f"Failed to start service: {e}")
        logger.error(traceback.format_exc())
        raise
