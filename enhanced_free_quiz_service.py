"""
Enhanced Free Quiz Generation Service
A sophisticated AI-powered quiz generator using Hugging Face Transformers
Provides ChatGPT-like quality without API costs
"""

import logging
import json
import re
from typing import List, Dict, Any, Optional
from flask import Flask, request, jsonify
from flask_cors import CORS
import nltk
from transformers import (
    T5ForConditionalGeneration, T5Tokenizer,
    BartForConditionalGeneration, BartTokenizer,
    pipeline,
    AutoTokenizer, AutoModelForSequenceClassification
)
import torch
from datetime import datetime
import warnings

# Suppress warnings for cleaner output
warnings.filterwarnings("ignore")

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

# Download required NLTK data
try:
    nltk.download('punkt', quiet=True)
    nltk.download('stopwords', quiet=True)
    nltk.download('wordnet', quiet=True)
except Exception as e:
    logger.warning(f"NLTK download warning: {e}")

# Initialize Flask app
app = Flask(__name__)
CORS(app)

class EnhancedFreeQuizGenerator:
    """
    Advanced quiz generation using multiple AI models for optimal quality
    """
    
    def __init__(self):
        self.models = {}
        self.tokenizers = {}
        self.pipelines = {}
        self.device = "cuda" if torch.cuda.is_available() else "cpu"
        logger.info(f"Using device: {self.device}")
        
        # Initialize models lazily to save memory
        self._initialize_core_models()
    
    def _initialize_core_models(self):
        """Initialize essential models"""
        try:
            # T5 for question generation
            logger.info("Loading T5 model for question generation...")
            self.tokenizers['t5'] = T5Tokenizer.from_pretrained('t5-small')
            self.models['t5'] = T5ForConditionalGeneration.from_pretrained('t5-small')
            
            # Question answering pipeline
            logger.info("Loading QA pipeline...")
            self.pipelines['qa'] = pipeline(
                'question-answering',
                model='distilbert-base-cased-distilled-squad',
                device=0 if self.device == "cuda" else -1
            )
            
            logger.info("Core models loaded successfully!")
            
        except Exception as e:
            logger.error(f"Error loading models: {e}")
            raise
    
    def _load_bart_if_needed(self):
        """Load BART model only when needed"""
        if 'bart' not in self.models:
            try:
                logger.info("Loading BART model...")
                self.tokenizers['bart'] = BartTokenizer.from_pretrained('facebook/bart-base')
                self.models['bart'] = BartForConditionalGeneration.from_pretrained('facebook/bart-base')
                logger.info("BART model loaded successfully!")
            except Exception as e:
                logger.error(f"Error loading BART: {e}")
    
    def extract_key_concepts(self, text: str, max_concepts: int = 10) -> List[str]:
        """Extract key concepts from text using NLP techniques"""
        try:
            # Use QA pipeline to identify important concepts
            concepts = []
            
            # Split into sentences
            sentences = nltk.sent_tokenize(text)
            
            # Extract concepts using keyword extraction
            words = nltk.word_tokenize(text.lower())
            
            # Filter for meaningful words (simple approach)
            meaningful_words = [
                word for word in words 
                if len(word) > 3 and word.isalpha() and 
                word not in nltk.corpus.stopwords.words('english')
            ]
            
            # Get top concepts by frequency
            from collections import Counter
            word_freq = Counter(meaningful_words)
            concepts = [word for word, freq in word_freq.most_common(max_concepts)]
            
            return concepts[:max_concepts]
            
        except Exception as e:
            logger.error(f"Error extracting concepts: {e}")
            return []
    
    def generate_questions_t5(self, context: str, num_questions: int = 5) -> List[Dict]:
        """Generate questions using T5 model"""
        try:
            questions = []
            
            # Split context into manageable chunks
            sentences = nltk.sent_tokenize(context)
            chunks = []
            
            current_chunk = ""
            for sentence in sentences:
                if len(current_chunk + sentence) < 400:  # Keep chunks manageable
                    current_chunk += " " + sentence
                else:
                    if current_chunk:
                        chunks.append(current_chunk.strip())
                    current_chunk = sentence
            
            if current_chunk:
                chunks.append(current_chunk.strip())
            
            # Generate questions from chunks
            for i, chunk in enumerate(chunks[:num_questions]):
                if len(chunk) < 20:  # Skip very short chunks
                    continue
                
                # Prepare input for T5
                input_text = f"generate question: {chunk}"
                
                # Tokenize and generate
                inputs = self.tokenizers['t5'].encode(
                    input_text, 
                    return_tensors='pt', 
                    max_length=512, 
                    truncation=True
                )
                
                # Generate question
                with torch.no_grad():
                    outputs = self.models['t5'].generate(
                        inputs,
                        max_length=100,
                        num_beams=4,
                        temperature=0.7,
                        do_sample=True,
                        early_stopping=True
                    )
                
                question = self.tokenizers['t5'].decode(outputs[0], skip_special_tokens=True)
                
                if question and len(question) > 5:
                    # Generate options using the context
                    options = self.generate_options_for_question(question, chunk)
                    
                    questions.append({
                        'question': question.capitalize() + "?",
                        'options': options,
                        'correct_answer': options[0] if options else 'Unable to determine',
                        'explanation': f"This question is based on: {chunk[:100]}..."
                    })
            
            return questions
            
        except Exception as e:
            logger.error(f"Error generating T5 questions: {e}")
            return []
    
    def generate_options_for_question(self, question: str, context: str) -> List[str]:
        """Generate multiple choice options for a question"""
        try:
            # Try to answer the question using the QA pipeline
            qa_result = self.pipelines['qa'](question=question, context=context)
            correct_answer = qa_result['answer']
            
            # Generate plausible distractors
            options = [correct_answer]
            
            # Extract key concepts for creating distractors
            concepts = self.extract_key_concepts(context, 5)
            
            # Create variations and distractors
            for concept in concepts[:3]:
                if concept.lower() not in correct_answer.lower():
                    options.append(concept.capitalize())
            
            # Add some generic distractors if needed
            while len(options) < 4:
                generic_options = [
                    "None of the above",
                    "All of the above", 
                    "Insufficient information",
                    "Not specified in the text"
                ]
                for opt in generic_options:
                    if opt not in options:
                        options.append(opt)
                        break
                else:
                    break
            
            return options[:4]
            
        except Exception as e:
            logger.error(f"Error generating options: {e}")
            return ["Option A", "Option B", "Option C", "Option D"]
    
    def generate_advanced_questions(self, context: str, num_questions: int = 5) -> List[Dict]:
        """Generate advanced questions using multiple techniques"""
        try:
            questions = []
            
            # Method 1: T5-based generation
            t5_questions = self.generate_questions_t5(context, num_questions // 2)
            questions.extend(t5_questions)
            
            # Method 2: Concept-based questions
            concepts = self.extract_key_concepts(context, 10)
            
            for i, concept in enumerate(concepts[:(num_questions - len(questions))]):
                # Create different types of questions
                question_types = [
                    f"What is the significance of {concept} in the given context?",
                    f"How does {concept} relate to the main topic?",
                    f"Define {concept} based on the provided information.",
                    f"What role does {concept} play in the described process?"
                ]
                
                question = question_types[i % len(question_types)]
                
                # Generate context-aware answer
                try:
                    qa_result = self.pipelines['qa'](question=question, context=context)
                    answer = qa_result['answer']
                except:
                    answer = concept
                
                options = self.generate_options_for_question(question, context)
                if answer not in options:
                    options[0] = answer
                
                questions.append({
                    'question': question,
                    'options': options,
                    'correct_answer': options[0],
                    'explanation': f"This question focuses on the concept '{concept}' from the provided material."
                })
            
            return questions[:num_questions]
            
        except Exception as e:
            logger.error(f"Error in advanced question generation: {e}")
            return []
    
    def generate_quiz(self, content: str, num_questions: int = 5, difficulty: str = 'medium') -> Dict[str, Any]:
        """
        Main quiz generation method
        """
        try:
            logger.info(f"Generating quiz with {num_questions} questions, difficulty: {difficulty}")
            
            if not content or len(content.strip()) < 50:
                return {
                    'success': False,
                    'error': 'Content too short for quiz generation',
                    'questions': []
                }
            
            # Generate questions using advanced methods
            questions = self.generate_advanced_questions(content, num_questions)
            
            if not questions:
                # Fallback to simpler method
                questions = self.generate_simple_questions(content, num_questions)
            
            # Enhance questions based on difficulty
            if difficulty == 'hard':
                questions = self.enhance_difficulty(questions)
            elif difficulty == 'easy':
                questions = self.simplify_questions(questions)
            
            return {
                'success': True,
                'questions': questions,
                'metadata': {
                    'generated_at': datetime.now().isoformat(),
                    'num_questions': len(questions),
                    'difficulty': difficulty,
                    'content_length': len(content),
                    'model_used': 'Enhanced Free AI (T5 + DistilBERT)'
                }
            }
            
        except Exception as e:
            logger.error(f"Error generating quiz: {e}")
            return {
                'success': False,
                'error': str(e),
                'questions': []
            }
    
    def generate_simple_questions(self, content: str, num_questions: int) -> List[Dict]:
        """Fallback simple question generation"""
        questions = []
        sentences = nltk.sent_tokenize(content)
        
        for i, sentence in enumerate(sentences[:num_questions]):
            if len(sentence) > 20:
                question = f"What does the following statement refer to: '{sentence[:50]}...'?"
                
                options = [
                    "Main concept from the text",
                    "Supporting detail",
                    "Example provided",
                    "Conclusion drawn"
                ]
                
                questions.append({
                    'question': question,
                    'options': options,
                    'correct_answer': options[0],
                    'explanation': f"Based on: {sentence}"
                })
        
        return questions
    
    def enhance_difficulty(self, questions: List[Dict]) -> List[Dict]:
        """Make questions more challenging"""
        for question in questions:
            # Add more complex language
            q_text = question['question']
            if not q_text.startswith('Analyze'):
                question['question'] = f"Analyze and explain: {q_text}"
        
        return questions
    
    def simplify_questions(self, questions: List[Dict]) -> List[Dict]:
        """Make questions easier"""
        for question in questions:
            # Simplify language
            q_text = question['question'].replace('significance', 'importance')
            q_text = q_text.replace('analyze', 'explain')
            question['question'] = q_text
        
        return questions

# Initialize the quiz generator
quiz_generator = EnhancedFreeQuizGenerator()

@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'service': 'Enhanced Free Quiz Generator',
        'device': quiz_generator.device,
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
        
        # Generate quiz
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
    """Test endpoint for debugging"""
    return jsonify({
        'message': 'Enhanced Free Quiz Service is running!',
        'device': quiz_generator.device,
        'models_loaded': list(quiz_generator.models.keys()),
        'pipelines_loaded': list(quiz_generator.pipelines.keys())
    })

if __name__ == '__main__':
    logger.info("Starting Enhanced Free Quiz Generation Service...")
    logger.info(f"Device: {quiz_generator.device}")
    logger.info("Service will be available at http://localhost:5002")
    
    app.run(
        host='0.0.0.0',
        port=5002,
        debug=False,  # Set to False for production
        threaded=True
    )
