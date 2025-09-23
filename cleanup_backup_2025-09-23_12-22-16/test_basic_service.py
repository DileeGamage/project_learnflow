#!/usr/bin/env python3

from flask import Flask, request, jsonify
from flask_cors import CORS
import logging

# Configure logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

app = Flask(__name__)
CORS(app)

@app.route('/', methods=['GET'])
def home():
    """Home endpoint"""
    return jsonify({'message': 'Quiz Service is running!', 'status': 'ok'})

@app.route('/health', methods=['GET'])
def health():
    """Health check endpoint"""
    logger.info("Health check requested")
    return jsonify({
        'status': 'healthy',
        'service': 'Test Quiz Service',
        'port': 5002
    })

@app.route('/generate-quiz', methods=['POST'])
def generate_quiz():
    """Simple quiz generation"""
    try:
        data = request.get_json() or {}
        
        # Return a simple test quiz
        result = {
            'success': True,
            'questions': {
                'multiple_choice': [
                    {
                        'question': 'Test question: What is this?',
                        'options': ['A) Option 1', 'B) Option 2', 'C) Option 3', 'D) Option 4'],
                        'correct_answer': 'A',
                        'explanation': 'This is a test question.',
                        'difficulty': 'easy'
                    }
                ]
            },
            'total_questions': 1,
            'generated_by': 'test_service'
        }
        
        return jsonify(result)
        
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500

if __name__ == '__main__':
    print("Starting test quiz service...")
    print("Service will be available at http://127.0.0.1:5002")
    
    # Try binding to all interfaces first
    try:
        app.run(host='0.0.0.0', port=5002, debug=True)
    except Exception as e:
        print(f"Failed to start on 0.0.0.0: {e}")
        # Fallback to localhost
        app.run(host='127.0.0.1', port=5002, debug=True)
