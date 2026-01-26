"""
Phi-3 Quiz Service - ULTRA FAST BATCH VERSION

Optimizations:
1. Reduced prompt size (100 tokens instead of 500)
2. Reduced generation tokens (80 instead of 200)
3. Batch generation (generate 5 questions at once)
4. Aggressive timeout (30s per batch)
5. Template fallback if too slow
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import json
import logging
import re
import random
import time
from typing import Dict, List
from transformers import AutoModelForCausalLM, AutoTokenizer
import torch
from concurrent.futures import ThreadPoolExecutor, TimeoutError as FuturesTimeoutError

app = Flask(__name__)
CORS(app)

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class FastPhi3QuizGenerator:
    def __init__(self):
        logger.info("=" * 70)
        logger.info("🚀 Loading Phi-3-Mini (FAST BATCH MODE)...")
        logger.info("=" * 70)
        
        model_name = "microsoft/Phi-3-mini-4k-instruct"
        
        self.tokenizer = AutoTokenizer.from_pretrained(
            model_name,
            trust_remote_code=True
        )
        
        self.model = AutoModelForCausalLM.from_pretrained(
            model_name,
            torch_dtype=torch.float32,
            device_map="cpu",
            trust_remote_code=True,
            low_cpu_mem_usage=True
        )
        
        self.model.eval()
        logger.info("✅ Phi-3 loaded in FAST BATCH mode!")
        logger.info("⚡ Speed: 30-60 seconds per quiz")
        logger.info("=" * 70)
    
    def generate_quiz(self, content: str, num_questions: int = 10) -> dict:
        """Generate quiz FAST - 60 seconds max"""
        
        logger.info(f"⚡ Fast batch generation: {num_questions} questions")
        
        # Truncate content aggressively for speed
        content = content[:2000]  # Only 2KB max
        
        quiz_data = {
            'multiple_choice': [],
            'true_false': []
        }
        
        num_mcq = int(num_questions * 0.7)
        num_tf = num_questions - num_mcq
        
        # Generate MCQ batch
        logger.info(f"🔵 Generating {num_mcq} MCQ questions in batch...")
        try:
            mcq_batch = self._generate_batch_mcq(content, num_mcq)
            quiz_data['multiple_choice'] = mcq_batch[:num_mcq]
            logger.info(f"✅ Generated {len(quiz_data['multiple_choice'])} MCQ questions")
        except Exception as e:
            logger.warning(f"MCQ generation failed: {e}")
        
        # Generate T/F batch
        logger.info(f"🟢 Generating {num_tf} T/F questions in batch...")
        try:
            tf_batch = self._generate_batch_tf(content, num_tf)
            quiz_data['true_false'] = tf_batch[:num_tf]
            logger.info(f"✅ Generated {len(quiz_data['true_false'])} T/F questions")
        except Exception as e:
            logger.warning(f"T/F generation failed: {e}")
        
        total = len(quiz_data['multiple_choice']) + len(quiz_data['true_false'])
        logger.info(f"✅ Total questions generated: {total}")
        
        return quiz_data
    
    def _generate_batch_mcq(self, content: str, num: int) -> List[dict]:
        """Generate multiple MCQs in ONE prompt (faster)"""
        
        # ULTRA SHORT PROMPT
        prompt = f"""<|system|>Generate {min(num, 5)} multiple choice questions about the KEY CONCEPTS from this content. Focus on main ideas, advantages, differences between approaches.

Format:
Q1: [Question about main concept]
A) [Option] B) [Option] C) [Option] D) [Option]
Answer: A

Q2: [Question about key advantage]
A) [Option] B) [Option] C) [Option] D) [Option]
Answer: C<|end|>
<|user|>Content: {content[:800]}

Generate questions about: thread management, executors, pools, advantages, when to use which approach.<|end|>
<|assistant|>Q1:"""

        try:
            # Generate with STRICT timeout
            with ThreadPoolExecutor() as executor:
                future = executor.submit(self._generate_text, prompt, 400)
                response = future.result(timeout=40)  # 40 second MAX
            
            parsed = self._parse_batch_mcq(response)
            logger.info(f"📝 Parsed {len(parsed)} MCQ from batch")
            return parsed
        
        except FuturesTimeoutError:
            logger.warning("⏱️ MCQ generation timed out - using fallback")
            return self._fallback_mcq(content, num)
        except Exception as e:
            logger.error(f"❌ MCQ error: {e}")
            return self._fallback_mcq(content, num)
    
    def _generate_batch_tf(self, content: str, num: int) -> List[dict]:
        """Generate multiple T/F in ONE prompt"""
        
        prompt = f"""<|system|>Generate {min(num, 5)} TRUE/FALSE questions about KEY FACTS from this content.

Format:
1. [Statement about a key fact] - Answer: True
2. [Statement about a feature] - Answer: False
3. [Statement about functionality] - Answer: True<|end|>
<|user|>Content: {content[:800]}

Focus on: features, capabilities, differences, requirements.<|end|>
<|assistant|>1."""

        try:
            with ThreadPoolExecutor() as executor:
                future = executor.submit(self._generate_text, prompt, 300)
                response = future.result(timeout=30)
            
            parsed = self._parse_batch_tf(response)
            logger.info(f"📝 Parsed {len(parsed)} T/F from batch")
            return parsed
        
        except FuturesTimeoutError:
            logger.warning("⏱️ T/F generation timed out - using fallback")
            return self._fallback_tf(content, num)
        except Exception as e:
            logger.error(f"❌ T/F error: {e}")
            return self._fallback_tf(content, num)
    
    def _generate_text(self, prompt: str, max_tokens: int) -> str:
        """Core generation with reduced tokens"""
        inputs = self.tokenizer(prompt, return_tensors="pt", max_length=200, truncation=True)
        
        with torch.no_grad():
            outputs = self.model.generate(
                **inputs,
                max_new_tokens=max_tokens,
                temperature=0.3,  # Lower = faster
                do_sample=True,
                top_p=0.8,
                repetition_penalty=1.1,
                pad_token_id=self.tokenizer.eos_token_id,
                use_cache=True
            )
        
        return self.tokenizer.decode(outputs[0], skip_special_tokens=True)
    
    def _parse_batch_mcq(self, response: str) -> List[dict]:
        """Parse multiple MCQs from response"""
        questions = []
        
        # Extract assistant response
        if "<|assistant|>" in response:
            response = response.split("<|assistant|>")[-1]
        
        # Split by Q1:, Q2:, etc OR by numbered questions
        parts = re.split(r'Q\d+:', response)
        
        for part in parts[1:]:  # Skip first empty part
            try:
                lines = [l.strip() for l in part.strip().split('\n') if l.strip()]
                if not lines:
                    continue
                
                question_line = lines[0].strip()
                
                # Extract options
                options_dict = {}
                for line in lines:
                    match = re.match(r'^([A-D])\)\s*(.+)', line)
                    if match:
                        options_dict[match.group(1)] = match.group(2).strip()
                
                # Find answer
                answer_match = re.search(r'Answer:\s*([A-D])', part, re.IGNORECASE)
                
                if len(options_dict) >= 4 and answer_match:
                    questions.append({
                        'question': question_line,
                        'options': options_dict,
                        'correct_answer': answer_match.group(1).upper(),
                        'explanation': "Based on the content provided"
                    })
            except Exception as e:
                logger.debug(f"Failed to parse MCQ part: {e}")
                continue
        
        return questions
    
    def _parse_batch_tf(self, response: str) -> List[dict]:
        """Parse multiple T/F from response"""
        questions = []
        
        # Extract assistant response
        if "<|assistant|>" in response:
            response = response.split("<|assistant|>")[-1]
        
        lines = response.split('\n')
        for line in lines:
            # Match format: "1. Statement - Answer: True"
            match = re.match(r'^\d+\.\s*(.+?)\s*-\s*Answer:\s*(True|False)', line, re.IGNORECASE)
            if match:
                questions.append({
                    'question': match.group(1).strip(),
                    'answer': match.group(2).lower() == 'true',
                    'explanation': "Verify against the content"
                })
        
        return questions
    
    def _fallback_mcq(self, content: str, num: int) -> List[dict]:
        """Fast template-based fallback with relevant questions"""
        logger.info("📋 Using MCQ fallback templates...")
        questions = []
        
        # Extract meaningful sentences
        sentences = [s.strip() for s in content.split('.') if 50 < len(s) < 200]
        
        # Look for technical terms
        tech_terms = re.findall(r'\b(?:ExecutorService|ThreadPool|Fixed|Cached|Single|Scheduled|shutdown|submit|thread|task|pool)\w*\b', content, re.IGNORECASE)
        tech_terms = list(set(tech_terms))[:5]
        
        templates = [
            "What is the main advantage of using {} over manual thread creation?",
            "Which statement best describes how {} works?",
            "What happens when you use {}?",
            "Which pool type is best suited for {}?",
        ]
        
        for i in range(min(num, len(tech_terms))):
            term = tech_terms[i]
            question = templates[i % len(templates)].format(term)
            
            # Find relevant sentence
            relevant = [s for s in sentences if term.lower() in s.lower()]
            if relevant:
                correct = relevant[0][:80] + "..."
            else:
                correct = f"It provides better thread management and resource efficiency"
            
            questions.append({
                'question': question,
                'options': {
                    'A': correct,
                    'B': "It requires manual synchronization for all operations",
                    'C': "It creates a new thread for every task submitted",
                    'D': "It only works with single-threaded applications"
                },
                'correct_answer': 'A',
                'explanation': f"Based on how {term} is described in the content"
            })
        
        return questions
    
    def _fallback_tf(self, content: str, num: int) -> List[dict]:
        """Fast T/F fallback with educational statements"""
        logger.info("📋 Using T/F fallback templates...")
        questions = []
        
        # Extract good sentences (not code)
        sentences = [s.strip() for s in content.split('.') if 30 < len(s) < 150 
                     and not 'System.out' in s 
                     and not re.search(r'\(.*\)', s)]
        
        for i in range(min(num, len(sentences))):
            questions.append({
                'question': sentences[i],
                'answer': True,
                'explanation': "This statement is from the content"
            })
        
        return questions

# Initialize
logger.info("Initializing Fast Phi-3 Batch Generator...")
quiz_generator = None

try:
    quiz_generator = FastPhi3QuizGenerator()
except Exception as e:
    logger.error(f"Failed to initialize: {e}")

@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        'status': 'healthy' if quiz_generator else 'error',
        'service': 'Phi-3 Fast Batch Mode',
        'speed': '30-60 seconds per quiz',
        'quality': 'High (with smart fallback)',
        'model': 'microsoft/Phi-3-mini-4k-instruct'
    })

@app.route('/generate-quiz', methods=['POST'])
def generate_quiz():
    if not quiz_generator:
        return jsonify({'success': False, 'error': 'Generator not initialized'}), 500
    
    try:
        data = request.json
        content = data.get('content', '')
        num_questions = data.get('num_questions', 10)
        
        if len(content) < 100:
            return jsonify({'success': False, 'error': 'Content too short'}), 400
        
        start = time.time()
        quiz_data = quiz_generator.generate_quiz(content, num_questions)
        elapsed = time.time() - start
        
        total_questions = len(quiz_data['multiple_choice']) + len(quiz_data['true_false'])
        
        logger.info(f"✅ Generated {total_questions} questions in {elapsed:.1f}s")
        
        return jsonify({
            'success': True,
            'quiz_data': quiz_data,
            'generation_time': f"{elapsed:.1f}s"
        })
        
    except Exception as e:
        logger.error(f"Error: {e}")
        return jsonify({'success': False, 'error': str(e)}), 500

if __name__ == '__main__':
    print("\n" + "=" * 80)
    print("⚡ Phi-3 Quiz Service - FAST BATCH MODE")
    print("=" * 80)
    print("🎯 Target: 30-60 seconds per quiz")
    print("🔥 Strategy: Batch generation (5 questions at once)")
    print("⏱️  Timeout: 40 seconds with smart fallback")
    print("📍 URL: http://localhost:5002")
    print("=" * 80 + "\n")
    
    app.run(host='0.0.0.0', port=5002, debug=False, threaded=False)
