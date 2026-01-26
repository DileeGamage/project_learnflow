"""
Simple Free Quiz Generation Service
Uses lightweight models for quick setup - no large downloads needed!
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import json
import logging
import re
import random
from typing import Dict, List, Any

app = Flask(__name__)
CORS(app)

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class SimpleFreeQuizGenerator:
    def __init__(self):
        """Initialize simple quiz generator with no model downloads"""
        logger.info("✅ Simple Free Quiz Generator initialized!")
        
    def generate_quiz(self, content: str, num_questions: int = 10, 
                     question_types: List[str] = None) -> Dict[str, Any]:
        """Generate quiz from content using keyword extraction"""
        
        if not content or len(content) < 50:
            raise ValueError("Content too short for quiz generation")
            
        logger.info(f"Generating {num_questions} questions...")
        
        # Extract sentences
        sentences = [s.strip() for s in re.split(r'[.!?]+', content) if len(s.strip()) > 30]
        
        if len(sentences) < num_questions:
            raise ValueError(f"Need at least {num_questions} substantial sentences")
        
        questions = []
        used_sentences = set()
        
        # Use at least half of requested questions
        types = question_types or ['multiple_choice', 'true_false']
        
        for i in range(num_questions):
            # Get unused sentence
            available = [s for idx, s in enumerate(sentences) if idx not in used_sentences]
            if not available:
                break
                
            sentence = random.choice(available)
            used_sentences.add(sentences.index(sentence))
            
            # Determine question type
            q_type = types[i % len(types)]
            
            if q_type == 'true_false':
                question = self._create_true_false(sentence, i + 1)
            else:
                question = self._create_multiple_choice(sentence, content, i + 1)
            
            if question:
                questions.append(question)
        
        return {
            'success': True,
            'questions': questions,
            'quiz_data': {
                'total_questions': len(questions),
                'question_types': list(set(q['type'] for q in questions))
            }
        }
    
    def _create_true_false(self, sentence: str, num: int) -> Dict:
        """Create true/false question"""
        # Find key phrase
        words = sentence.split()
        if len(words) < 5:
            return None
            
        # Remove some words to make it questionable
        if random.random() > 0.5:
            # Keep original (TRUE)
            return {
                'question_number': num,
                'type': 'true_false',
                'question_text': sentence,
                'correct_answer': 'true',
                'options': ['true', 'false'],
                'explanation': 'This statement is directly from the text.',
                'points': 1,
                'difficulty': 'easy'
            }
        else:
            # Modify sentence (FALSE)
            # Simple negation
            modified = sentence.replace(' is ', ' is not ')
            if modified == sentence:
                modified = sentence.replace(' are ', ' are not ')
            if modified == sentence:
                modified = sentence.replace(' can ', ' cannot ')
                
            return {
                'question_number': num,
                'type': 'true_false',
                'question_text': modified,
                'correct_answer': 'false',
                'options': ['true', 'false'],
                'explanation': 'This statement was modified from the original text.',
                'points': 1,
                'difficulty': 'easy'
            }
    
    def _create_multiple_choice(self, sentence: str, content: str, num: int) -> Dict:
        """Create multiple choice question"""
        # Extract key terms (words longer than 4 chars)
        words = [w.strip('.,;:!?()[]{}') for w in sentence.split()]
        key_words = [w for w in words if len(w) > 4 and w[0].isupper()]
        
        if not key_words:
            key_words = [w for w in words if len(w) > 6]
        
        if not key_words:
            return None
            
        answer = random.choice(key_words)
        
        # Create question by blanking the answer
        question_text = sentence.replace(answer, '______')
        
        # Generate distractors
        all_words = content.split()
        similar_words = [w.strip('.,;:!?()[]{}') for w in all_words 
                        if len(w) > 4 and w != answer and w[0].isupper()]
        
        if len(similar_words) < 3:
            similar_words = [w for w in all_words if len(w) > 6 and w != answer]
        
        distractors = random.sample(similar_words, min(3, len(similar_words)))
        while len(distractors) < 3:
            distractors.append(f"Option{len(distractors) + 1}")
        
        options = [answer] + distractors[:3]
        random.shuffle(options)
        
        return {
            'question_number': num,
            'type': 'multiple_choice',
            'question_text': question_text,
            'correct_answer': answer,
            'options': options,
            'explanation': f'The correct answer is {answer} based on the text.',
            'points': 1,
            'difficulty': 'medium'
        }


# Global instance
quiz_gen = None

@app.route('/')
def home():
    return jsonify({
        'service': 'Simple Free Quiz Generation',
        'status': 'running',
        'models': 'No downloads needed!',
        'endpoints': {
            '/health': 'GET - Health check',
            '/generate-quiz': 'POST - Generate quiz'
        }
    })

@app.route('/health')
def health():
    logger.info("Health check: healthy")
    return jsonify({'status': 'healthy', 'service': 'simple_free_quiz'})

@app.route('/generate-quiz', methods=['POST'])
def generate_quiz():
    global quiz_gen
    
    try:
        if quiz_gen is None:
            quiz_gen = SimpleFreeQuizGenerator()
        
        data = request.json
        content = data.get('content', '')
        num_questions = data.get('num_questions', 10)
        question_types = data.get('question_types', ['multiple_choice', 'true_false'])
        
        logger.info(f"Generating quiz with {num_questions} questions...")
        
        result = quiz_gen.generate_quiz(content, num_questions, question_types)
        
        logger.info(f"✅ Generated {len(result['questions'])} questions")
        return jsonify(result)
        
    except Exception as e:
        logger.error(f"❌ Quiz generation error: {str(e)}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500


if __name__ == '__main__':
    print("🚀 Starting Simple Free Quiz Generation Service...")
    print("✅ No model downloads needed!")
    print("📍 Available at: http://localhost:5002")
    print()
    app.run(debug=True, host='0.0.0.0', port=5002)
