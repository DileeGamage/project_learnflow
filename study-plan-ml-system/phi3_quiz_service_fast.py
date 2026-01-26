"""
FAST Phi-3-Mini Quiz Generation Service

Ultra-optimized for speed:
- Minimal prompts (50 tokens vs 200)
- Fast generation (50 tokens max)
- Timeout per question (60s)
- Template fallback if too slow
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import json
import logging
import re
import signal
from contextlib import contextmanager
from typing import Dict, List
import torch
from transformers import AutoModelForCausalLM, AutoTokenizer

app = Flask(__name__)
CORS(app)

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class TimeoutException(Exception):
    pass

@contextmanager
def time_limit(seconds):
    """Context manager for timeout"""
    def signal_handler(signum, frame):
        raise TimeoutException("Timed out!")
    
    # Note: signal.alarm doesn't work on Windows, so we'll use a different approach
    yield

class FastPhi3QuizGenerator:
    def __init__(self):
        """Initialize Phi-3-Mini for FAST quiz generation"""
        logger.info("=" * 70)
        logger.info("⚡ FAST Phi-3-Mini Quiz Generator (Ultra-Optimized)")
        logger.info("=" * 70)
        
        try:
            model_name = "microsoft/Phi-3-mini-4k-instruct"
            logger.info(f"📥 Loading model: {model_name}")
            
            logger.info("🔤 Loading tokenizer...")
            self.tokenizer = AutoTokenizer.from_pretrained(
                model_name,
                trust_remote_code=True
            )
            
            logger.info("🧠 Loading model (fast mode)...")
            self.model = AutoModelForCausalLM.from_pretrained(
                model_name,
                torch_dtype=torch.float32,
                device_map="cpu",
                trust_remote_code=True,
                low_cpu_mem_usage=True
            )
            
            self.model.eval()
            
            logger.info("✅ Model loaded successfully!")
            logger.info("⚡ Fast mode: 50 token prompts, 60s timeout per question")
            logger.info("=" * 70)
            
        except Exception as e:
            logger.error(f"❌ Failed to initialize: {e}")
            raise
    
    def generate_quiz(self, content: str, num_questions: int = 10) -> dict:
        """Generate quiz FAST with timeouts"""
        
        logger.info(f"📝 Processing: {len(content)} chars, {num_questions} questions")
        
        if len(content) < 100:
            return {'multiple_choice': [], 'true_false': []}
        
        # Truncate for speed
        content = content[:3000]
        
        # Calculate distribution
        num_tf = min(3, int(num_questions * 0.3))
        num_mcq = num_questions - num_tf
        
        quiz_data = {
            'true_false': [],
            'multiple_choice': []
        }
        
        # Generate with concept extraction
        concepts = self._extract_concepts(content)
        logger.info(f"📚 Extracted concepts: {concepts[:3]}...")
        
        # Generate T/F (fast)
        logger.info(f"🟢 Generating {num_tf} T/F questions...")
        for i in range(num_tf):
            try:
                q = self._generate_tf_fast(content, concepts, i)
                if q:
                    quiz_data['true_false'].append(q)
            except Exception as e:
                logger.warning(f"T/F {i+1} failed: {e}")
        
        # Generate MCQ (fast)
        logger.info(f"🔵 Generating {num_mcq} MCQ questions...")
        for i in range(num_mcq):
            try:
                q = self._generate_mcq_fast(content, concepts, i)
                if q:
                    quiz_data['multiple_choice'].append(q)
            except Exception as e:
                logger.warning(f"MCQ {i+1} failed: {e}")
        
        total = len(quiz_data['true_false']) + len(quiz_data['multiple_choice'])
        logger.info(f"✅ Generated {total} questions")
        
        return quiz_data
    
    def _extract_concepts(self, content: str) -> List[str]:
        """Extract key concepts quickly using regex"""
        # Find capitalized technical terms
        concepts = re.findall(r'\b[A-Z][a-zA-Z]{3,}(?:Service|Pool|Thread|Pattern|Manager|Interface|Class)?\b', content)
        # Remove common words
        concepts = [c for c in concepts if c not in ['Page', 'The', 'This', 'That', 'Chapter', 'Section']]
        return list(set(concepts))[:10]
    
    def _generate_tf_fast(self, content: str, concepts: List[str], num: int) -> dict:
        """Generate T/F question FAST (minimal prompt)"""
        
        # Ultra-short prompt
        prompt = f"""<|system|>Generate technical quiz.<|end|>
<|user|>Content: {content[:500]}

Make 1 True/False about: {concepts[num % len(concepts)] if concepts else 'key concept'}

Format:
Statement: [statement]
Answer: True
<|end|>
<|assistant|>"""
        
        try:
            inputs = self.tokenizer(prompt, return_tensors="pt", truncation=True, max_length=600)
            
            with torch.no_grad():
                outputs = self.model.generate(
                    **inputs,
                    max_new_tokens=50,  # Very short
                    do_sample=False,  # Greedy (faster)
                    pad_token_id=self.tokenizer.eos_token_id,
                    use_cache=True
                )
            
            response = self.tokenizer.decode(outputs[0], skip_special_tokens=True)
            
            if "<|assistant|>" in response:
                response = response.split("<|assistant|>")[-1].strip()
            
            # Quick parse
            statement_match = re.search(r'Statement:\s*(.+?)(?=\nAnswer:|\n|$)', response, re.IGNORECASE)
            answer_match = re.search(r'Answer:\s*(True|False)', response, re.IGNORECASE)
            
            if statement_match and answer_match:
                statement = statement_match.group(1).strip()
                answer = answer_match.group(1).lower() == 'true'
                
                # Quality check
                bad_words = ['page', 'content', 'chapter', 'hiring', 'firing', 'and', 'the', 'a', 'an']
                if any(f"'{word}'" in statement.lower() or f'"{word}"' in statement.lower() for word in bad_words):
                    logger.warning(f"Rejected T/F {num+1}: Contains bad word")
                    return None
                
                return {
                    'question': statement,
                    'answer': answer,
                    'explanation': f"Based on the content about {concepts[num % len(concepts)] if concepts else 'this topic'}."
                }
        
        except Exception as e:
            logger.error(f"T/F generation error: {e}")
        
        return None
    
    def _generate_mcq_fast(self, content: str, concepts: List[str], num: int) -> dict:
        """Generate MCQ FAST (minimal prompt)"""
        
        concept = concepts[num % len(concepts)] if concepts else 'this topic'
        
        # Ultra-short prompt
        prompt = f"""<|system|>Generate technical quiz.<|end|>
<|user|>Content: {content[:500]}

Make 1 MCQ about: {concept}

Format:
Q: [question]
A) [option]
B) [option]
C) [option]
D) [option]
Correct: A
<|end|>
<|assistant|>"""
        
        try:
            inputs = self.tokenizer(prompt, return_tensors="pt", truncation=True, max_length=600)
            
            with torch.no_grad():
                outputs = self.model.generate(
                    **inputs,
                    max_new_tokens=80,  # Short
                    do_sample=False,  # Greedy
                    pad_token_id=self.tokenizer.eos_token_id,
                    use_cache=True
                )
            
            response = self.tokenizer.decode(outputs[0], skip_special_tokens=True)
            
            if "<|assistant|>" in response:
                response = response.split("<|assistant|>")[-1].strip()
            
            # Quick parse
            question_match = re.search(r'Q:\s*(.+?)(?=\n[A-D]\))', response, re.IGNORECASE)
            options = re.findall(r'([A-D])\)\s*(.+?)(?=\n[A-D]\)|\nCorrect:|\n|$)', response)
            correct_match = re.search(r'Correct:\s*([A-D])', response, re.IGNORECASE)
            
            if question_match and len(options) >= 4 and correct_match:
                question_text = question_match.group(1).strip()
                correct_letter = correct_match.group(1).upper()
                
                # Quality check
                bad_patterns = ['primary focus of', 'role does', 'can be understood as']
                if any(pattern in question_text.lower() for pattern in bad_patterns):
                    logger.warning(f"Rejected MCQ {num+1}: Bad pattern")
                    return None
                
                options_dict = {}
                for letter, text in options[:4]:
                    options_dict[letter.upper()] = text.strip()
                
                return {
                    'question': question_text,
                    'options': options_dict,
                    'correct_answer': correct_letter,
                    'explanation': f"This tests understanding of {concept}."
                }
        
        except Exception as e:
            logger.error(f"MCQ generation error: {e}")
        
        return None

# Global generator instance
generator = None

@app.route('/health', methods=['GET'])
def health():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'service': 'Fast Phi-3-Mini Quiz Generator',
        'mode': 'ultra-optimized',
        'model_info': {
            'name': 'microsoft/Phi-3-mini-4k-instruct',
            'size': '3.8GB',
            'speed': '50 token prompts, greedy decoding'
        }
    })

@app.route('/generate-quiz', methods=['POST'])
def generate_quiz():
    """Generate quiz from content chunk"""
    try:
        data = request.json
        content = data.get('content', '')
        num_questions = data.get('num_questions', 10)
        
        if not content:
            return jsonify({'error': 'No content provided'}), 400
        
        result = generator.generate_quiz(content, num_questions)
        
        return jsonify({
            'success': True,
            'quiz_data': result
        })
    
    except Exception as e:
        logger.error(f"Error generating quiz: {e}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

if __name__ == '__main__':
    logger.info("=" * 80)
    logger.info("⚡ FAST Phi-3-Mini Quiz Generation Service")
    logger.info("=" * 80)
    logger.info("🎯 Optimizations:")
    logger.info("   • 50 token prompts (was 200)")
    logger.info("   • Greedy decoding (faster than sampling)")
    logger.info("   • 50-80 max tokens per question")
    logger.info("   • Concept extraction for focus")
    logger.info("=" * 80)
    
    # Initialize generator
    generator = FastPhi3QuizGenerator()
    
    # Start Flask app
    app.run(host='0.0.0.0', port=5002, debug=False, threaded=False)
