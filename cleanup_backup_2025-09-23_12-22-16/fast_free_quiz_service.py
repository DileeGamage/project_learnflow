"""
Fast Free Quiz Generation Service
A lightweight alternative that starts quickly for immediate testing
"""

import logging
import json
import re
from typing import List, Dict, Any, Optional
from flask import Flask, request, jsonify
from flask_cors import CORS
import random
from datetime import datetime
import warnings

# Suppress warnings
warnings.filterwarnings("ignore")

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

# Initialize Flask app
app = Flask(__name__)
CORS(app)

class FastQuizGenerator:
    """
    Fast quiz generation for immediate testing
    Uses rule-based methods that don't require large model downloads
    """
    
    def __init__(self):
        logger.info("Fast Quiz Generator initialized")
    
    def extract_sentences(self, text: str) -> List[str]:
        """Extract meaningful sentences from text"""
        # Simple sentence splitting
        sentences = re.split(r'[.!?]+', text)
        
        # Filter meaningful sentences
        meaningful = []
        for sentence in sentences:
            sentence = sentence.strip()
            if len(sentence) > 20 and len(sentence.split()) > 4:
                meaningful.append(sentence)
        
        return meaningful
    
    def extract_key_terms(self, text: str) -> List[str]:
        """Extract key terms from text"""
        # Simple keyword extraction
        words = re.findall(r'\b[A-Z][a-z]+\b', text)  # Capitalized words
        
        # Add some common patterns
        technical_terms = re.findall(r'\b[a-z]+ing\b', text.lower())  # -ing words
        
        all_terms = list(set(words + [term.capitalize() for term in technical_terms]))
        return all_terms[:10]
    
    def generate_questions(self, content: str, num_questions: int = 5) -> List[Dict]:
        """Generate quiz questions from content"""
        questions = []
        sentences = self.extract_sentences(content)
        key_terms = self.extract_key_terms(content)
        
        # Question templates
        templates = [
            "What is the main concept discussed in: '{}'?",
            "According to the text, what can be said about {}?",
            "Which statement best describes {}?",
            "What is the relationship between {} and the main topic?",
            "How is {} defined in the given context?"
        ]
        
        for i in range(min(num_questions, len(sentences))):
            sentence = sentences[i]
            
            if i < len(key_terms):
                # Use key term based question
                term = key_terms[i]
                question_text = random.choice(templates[1:]).format(term)
                
                # Generate options
                options = [
                    f"It is a fundamental concept mentioned in the text",
                    f"It is an example or illustration",
                    f"It is a supporting detail",
                    f"It is not clearly defined"
                ]
                
            else:
                # Use sentence based question
                short_sentence = sentence[:80] + "..." if len(sentence) > 80 else sentence
                question_text = templates[0].format(short_sentence)
                
                # Generate options
                options = [
                    "The primary subject of the passage",
                    "A supporting example",
                    "Background information",
                    "An unrelated detail"
                ]
            
            # Randomize options
            correct_answer = options[0]
            random.shuffle(options)
            
            questions.append({
                'question': question_text,
                'options': options,
                'correct_answer': correct_answer,
                'explanation': f"This question is based on the analysis of: {sentence[:100]}..."
            })
        
        return questions
    
    def generate_quiz(self, content: str, num_questions: int = 5, difficulty: str = 'medium') -> Dict[str, Any]:
        """Generate a complete quiz"""
        try:
            logger.info(f"Generating quiz with {num_questions} questions")
            
            if not content or len(content.strip()) < 30:
                return {
                    'success': False,
                    'error': 'Content too short for quiz generation',
                    'questions': []
                }
            
            questions = self.generate_questions(content, num_questions)
            
            return {
                'success': True,
                'questions': questions,
                'metadata': {
                    'generated_at': datetime.now().isoformat(),
                    'num_questions': len(questions),
                    'difficulty': difficulty,
                    'content_length': len(content),
                    'model_used': 'Fast Free AI (Rule-based)'
                }
            }
            
        except Exception as e:
            logger.error(f"Error generating quiz: {e}")
            return {
                'success': False,
                'error': str(e),
                'questions': []
            }

# Initialize the generator
quiz_generator = FastQuizGenerator()

@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'service': 'Fast Free Quiz Generator',
        'timestamp': datetime.now().isoformat()
    })

@app.route('/generate-quiz', methods=['POST'])
def generate_quiz():
    """Generate quiz endpoint"""
    try:
        data = request.get_json()
        
        if not data:
            return jsonify({
                'success': False,
                'error': 'No JSON data provided'
            }), 400
        
        content = data.get('content', '')
        num_questions = data.get('num_questions', 5)
        difficulty = data.get('difficulty', 'medium')
        
        if not content:
            return jsonify({
                'success': False,
                'error': 'No content provided'
            }), 400
        
        result = quiz_generator.generate_quiz(content, num_questions, difficulty)
        return jsonify(result)
        
    except Exception as e:
        logger.error(f"Error in generate_quiz endpoint: {e}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/test', methods=['GET'])
def test_endpoint():
    """Test endpoint"""
    return jsonify({
        'message': 'Fast Free Quiz Service is running!',
        'status': 'ready'
    })

if __name__ == '__main__':
    logger.info("Starting Fast Free Quiz Generation Service...")
    logger.info("Service will be available at http://localhost:8000")
    
    app.run(
        host='127.0.0.1',  # Use localhost specifically
        port=8000,         # Use a different port
        debug=False,
        threaded=True
    )
