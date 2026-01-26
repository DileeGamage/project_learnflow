"""
Phi-3 Quiz Service with SMART FALLBACK
- Uses Phi-3 AI when fast enough
- Falls back to SENTENCE-BASED extraction (not single words)
- Extracts complete concepts like "ExecutorService manages thread pools"
"""

import logging
from flask import Flask, request, jsonify
from flask_cors import CORS
import torch
from transformers import AutoTokenizer, AutoModelForCausalLM
from concurrent.futures import ThreadPoolExecutor, TimeoutError as FuturesTimeoutError
from typing import List
import re
import time

# Configure logging
logging.basicConfig(level=logging.INFO, format='%(levelname)s: %(message)s')
logger = logging.getLogger(__name__)

app = Flask(__name__)
CORS(app)

class SmartPhi3QuizGenerator:
    def __init__(self):
        logger.info("🚀 Loading Phi-3-Mini model...")
        self.tokenizer = AutoTokenizer.from_pretrained(
            "microsoft/Phi-3-mini-4k-instruct",
            trust_remote_code=True
        )
        self.model = AutoModelForCausalLM.from_pretrained(
            "microsoft/Phi-3-mini-4k-instruct",
            torch_dtype=torch.float32,
            device_map="cpu",
            trust_remote_code=True,
            low_cpu_mem_usage=True
        )
        logger.info("✅ Phi-3 model loaded!")
    
    def generate_quiz(self, content: str, num_questions: int = 10) -> dict:
        """Generate quiz with smart AI + fallback"""
        start_time = time.time()
        
        # Truncate content
        content = content[:2000]
        
        # Calculate split (70% MCQ, 30% T/F)
        num_mcq = int(num_questions * 0.7)
        num_tf = num_questions - num_mcq
        
        # Try AI first, fallback if too slow
        mcq_questions = self._generate_mcq_smart(content, num_mcq)
        tf_questions = self._generate_tf_smart(content, num_tf)
        
        elapsed = time.time() - start_time
        return {
            'multiple_choice': mcq_questions,
            'true_false': tf_questions,
            'generation_time': f"{elapsed:.1f}s"
        }
    
    def _generate_mcq_smart(self, content: str, num: int) -> List[dict]:
        """Try AI for 60s, then fallback to SMART extraction"""
        logger.info(f"⚡ Attempting AI generation for {num} MCQs...")
        
        prompt = f"""<|system|>Generate {num} multiple choice questions about KEY CONCEPTS.

Format:
Q1: What is the main advantage of using ExecutorService?
A) It manages thread pools efficiently
B) It requires no configuration
C) It works only with single threads
D) It blocks all operations
Answer: A<|end|>
<|user|>Content: {content[:700]}<|end|>
<|assistant|>Q1:"""
        
        try:
            # Try AI with 60-second timeout
            with ThreadPoolExecutor() as executor:
                future = executor.submit(self._generate_ai_text, prompt, 250)
                response = future.result(timeout=60)
            
            parsed = self._parse_mcq_batch(response)
            if parsed and len(parsed) > 0:
                logger.info(f"✅ AI generated {len(parsed)} MCQs")
                return parsed
            else:
                raise ValueError("AI returned empty")
        
        except (FuturesTimeoutError, Exception) as e:
            logger.warning(f"⏱️ AI timeout or error ({e}) - using SMART fallback")
            return self._smart_fallback_mcq(content, num)
    
    def _generate_tf_smart(self, content: str, num: int) -> List[dict]:
        """Try AI for 45s, then fallback to SMART extraction"""
        logger.info(f"⚡ Attempting AI generation for {num} T/Fs...")
        
        prompt = f"""<|system|>Generate {num} TRUE/FALSE questions.

Format:
1. ExecutorService manages thread pools efficiently - Answer: True
2. Fixed thread pools create new threads for each task - Answer: False<|end|>
<|user|>Content: {content[:700]}<|end|>
<|assistant|>1."""
        
        try:
            with ThreadPoolExecutor() as executor:
                future = executor.submit(self._generate_ai_text, prompt, 200)
                response = future.result(timeout=45)
            
            parsed = self._parse_tf_batch(response)
            if parsed and len(parsed) > 0:
                logger.info(f"✅ AI generated {len(parsed)} T/Fs")
                return parsed
            else:
                raise ValueError("AI returned empty")
        
        except (FuturesTimeoutError, Exception) as e:
            logger.warning(f"⏱️ AI timeout or error ({e}) - using SMART fallback")
            return self._smart_fallback_tf(content, num)
    
    def _generate_ai_text(self, prompt: str, max_tokens: int) -> str:
        """Generate text using Phi-3"""
        inputs = self.tokenizer(prompt, return_tensors="pt")
        
        with torch.no_grad():
            outputs = self.model.generate(
                inputs['input_ids'],
                max_new_tokens=max_tokens,
                temperature=0.4,
                top_p=0.85,
                do_sample=True,
                pad_token_id=self.tokenizer.eos_token_id,
                use_cache=True
            )
        
        response = self.tokenizer.decode(outputs[0], skip_special_tokens=True)
        response = response.split("<|assistant|>")[-1].strip()
        return response
    
    def _parse_mcq_batch(self, text: str) -> List[dict]:
        """Parse MCQ batch format"""
        questions = []
        
        # Split by Q1:, Q2:, etc
        parts = re.split(r'Q\d+:', text)[1:]
        
        for part in parts[:5]:
            try:
                # Extract question
                question_match = re.search(r'^(.+?)(?=\n[A-D]\))', part, re.DOTALL)
                if not question_match:
                    continue
                question = question_match.group(1).strip()
                
                # Extract options
                options = {}
                for letter in ['A', 'B', 'C', 'D']:
                    opt_match = re.search(rf'{letter}\)\s*(.+?)(?=\n[A-D]\)|Answer:|$)', part, re.DOTALL)
                    if opt_match:
                        options[letter] = opt_match.group(1).strip()[:100]
                
                # Extract answer
                answer_match = re.search(r'Answer:\s*([A-D])', part)
                if not answer_match or len(options) < 4:
                    continue
                
                questions.append({
                    'question': question[:200],
                    'options': options,
                    'correct_answer': answer_match.group(1),
                    'explanation': "Generated by Phi-3 AI"
                })
            except:
                continue
        
        return questions
    
    def _parse_tf_batch(self, text: str) -> List[dict]:
        """Parse T/F batch format"""
        questions = []
        
        # Split by numbers
        parts = re.split(r'\d+\.', text)[1:]
        
        for part in parts[:5]:
            try:
                # Extract statement and answer
                match = re.search(r'(.+?)\s*-\s*Answer:\s*(True|False)', part, re.IGNORECASE)
                if not match:
                    continue
                
                statement = match.group(1).strip()
                answer = match.group(2).strip().lower() == 'true'
                
                if len(statement) > 20:
                    questions.append({
                        'question': statement[:200],
                        'answer': answer,
                        'explanation': "Generated by Phi-3 AI"
                    })
            except:
                continue
        
        return questions
    
    def _smart_fallback_mcq(self, content: str, num: int) -> List[dict]:
        """
        SMART FALLBACK: Extract COMPLETE CONCEPTS
        - Find sentences like "ExecutorService is a framework for..."
        - Extract method descriptions like "submit() queues a task for..."
        - NOT single words like "thread", "submit"
        """
        logger.info("📚 SMART FALLBACK: Extracting complete concepts...")
        questions = []
        
        # PATTERN 1: "X is Y" or "X provides Y" (complete definitions)
        definition_pattern = r'([A-Z][a-zA-Z]+(?:Service|Pool|Executor|Framework)?)\s+(?:is|provides|manages)\s+([^.!?]{15,120}[.!?])'
        definitions = []
        for match in re.finditer(definition_pattern, content):
            term = match.group(1).strip()
            desc = match.group(2).strip()
            # Filter: No pronouns (It, The, This), no HTML entities, good length
            if (term not in ['It', 'The', 'This', 'That', 'These', 'Those'] and
                '&#' not in desc and 
                len(desc) > 20 and
                len(term) > 3):
                definitions.append((term, desc))
        
        logger.info(f"Found {len(definitions)} complete definitions")
        
        # PATTERN 2: Technical advantages/purposes
        advantage_pattern = r'(?:advantage|benefit|purpose|main feature)(?:\s+of)?(?:\s+using)?\s+([A-Z][a-zA-Z]+)\s+(?:is|are)\s+([^.!?]{15,100})'
        advantages = []
        for match in re.finditer(advantage_pattern, content, re.IGNORECASE):
            term = match.group(1).strip()
            adv = match.group(2).strip()
            # Filter out pronouns
            if (term not in ['It', 'The', 'This', 'That'] and
                '&#' not in adv and 
                len(adv) > 20 and
                len(term) > 3):
                advantages.append((term, adv))
        
        logger.info(f"Found {len(advantages)} advantages")
        
        # PATTERN 3: Method descriptions
        method_pattern = r'(new[A-Z][a-zA-Z]+Pool|submit|execute|shutdown|awaitTermination|shutdownNow)\(\)\s+([^.!?]{15,100})'
        methods = []
        for match in re.finditer(method_pattern, content):
            method = match.group(1).strip()
            desc = match.group(2).strip()
            # Must have actual description, not HTML entities
            if '&#' not in desc and len(desc) > 20 and len(method) > 3:
                methods.append((method + "()", desc))
        
        logger.info(f"Found {len(methods)} method descriptions")
        
        # Generate questions from extracted concepts
        all_concepts = definitions + advantages + methods
        
        for i, (term, description) in enumerate(all_concepts[:num]):
            # Create question based on concept type
            if any(word in description.lower() for word in ['advantage', 'benefit', 'better']):
                question = f"What is the main advantage of using {term}?"
            elif '()' in term:
                question = f"What does the {term} method do?"
            else:
                question = f"What is {term}?"
            
            # Use the ACTUAL description as correct answer
            correct_answer = description[:85] + ("..." if len(description) > 85 else "")
            
            # Generate plausible distractors
            distractors = [
                "It creates a new thread for every operation",
                "It requires manual synchronization for all tasks",
                "It only works in single-threaded environments"
            ]
            
            options = {
                'A': correct_answer,
                'B': distractors[0],
                'C': distractors[1],
                'D': distractors[2]
            }
            
            questions.append({
                'question': question,
                'options': options,
                'correct_answer': 'A',
                'explanation': f"From content: {term} {description[:60]}..."
            })
        
        # If we didn't find enough concepts, add DIFFERENT generic questions
        generic_mcq_bank = [
            {
                'question': "What is a key benefit of using thread pools?",
                'options': {
                    'A': "Reuses threads to reduce overhead",
                    'B': "Creates unlimited threads",
                    'C': "Requires no configuration",
                    'D': "Works only with single threads"
                },
                'correct_answer': 'A',
                'explanation': "Thread pools reuse threads efficiently"
            },
            {
                'question': "What is the main purpose of an ExecutorService?",
                'options': {
                    'A': "To manage thread execution and lifecycle",
                    'B': "To compile Java code",
                    'C': "To handle database connections",
                    'D': "To parse XML files"
                },
                'correct_answer': 'A',
                'explanation': "ExecutorService manages thread execution"
            },
            {
                'question': "Which method submits a task to an ExecutorService?",
                'options': {
                    'A': "submit() or execute()",
                    'B': "start() or run()",
                    'C': "init() or begin()",
                    'D': "launch() or trigger()"
                },
                'correct_answer': 'A',
                'explanation': "submit() and execute() are used to submit tasks"
            },
            {
                'question': "What happens when you call shutdown() on an ExecutorService?",
                'options': {
                    'A': "It stops accepting new tasks but completes existing ones",
                    'B': "It immediately terminates all running tasks",
                    'C': "It pauses all tasks temporarily",
                    'D': "It restarts the thread pool"
                },
                'correct_answer': 'A',
                'explanation': "shutdown() allows existing tasks to complete"
            },
            {
                'question': "What is the difference between fixed and cached thread pools?",
                'options': {
                    'A': "Fixed has constant threads, cached creates threads as needed",
                    'B': "Fixed is slower, cached is faster",
                    'C': "Fixed uses more memory, cached uses less",
                    'D': "Fixed is deprecated, cached is recommended"
                },
                'correct_answer': 'A',
                'explanation': "Fixed thread pools maintain a constant number of threads"
            },
            {
                'question': "When should you use a single thread executor?",
                'options': {
                    'A': "For sequential task execution with guaranteed order",
                    'B': "For maximum parallel performance",
                    'C': "For distributed systems only",
                    'D': "When you need multiple concurrent threads"
                },
                'correct_answer': 'A',
                'explanation': "Single thread executors ensure sequential execution"
            },
            {
                'question': "What is the advantage of using Executors factory methods?",
                'options': {
                    'A': "Simplified thread pool creation with common configurations",
                    'B': "Automatic memory management",
                    'C': "Built-in error handling",
                    'D': "Guaranteed deadlock prevention"
                },
                'correct_answer': 'A',
                'explanation': "Executors provide convenient factory methods for common thread pool types"
            }
        ]
        
        # Add different generic questions if needed
        generic_index = 0
        while len(questions) < num and generic_index < len(generic_mcq_bank):
            questions.append(generic_mcq_bank[generic_index])
            generic_index += 1
        
        logger.info(f"✅ Generated {len(questions)} MCQs ({len(all_concepts)} from content + {generic_index} generic)")
        return questions[:num]
    
    def _smart_fallback_tf(self, content: str, num: int) -> List[dict]:
        """
        SMART FALLBACK: Extract COMPLETE FACTUAL SENTENCES
        - Find sentences like "ExecutorService manages thread pools"
        - NOT fragments like "g conditions without manual intervention"
        """
        logger.info("📚 SMART FALLBACK: Extracting factual sentences...")
        questions = []
        
        # Extract sentences with specific patterns
        factual_patterns = [
            # Pattern: "X provides/manages/allows Y"
            r'([A-Z][a-zA-Z]+(?:Service|Pool|Executor)?)\s+(?:provides|manages|allows|enables)\s+([^.!?]{20,120}[.!?])',
            # Pattern: "X is used for Y"
            r'([A-Z][a-zA-Z]+)\s+is\s+used\s+for\s+([^.!?]{20,100}[.!?])',
            # Pattern: "The X method Y"
            r'The\s+([a-zA-Z]+)\s+method\s+([^.!?]{20,100}[.!?])',
        ]
        
        factual_sentences = []
        for pattern in factual_patterns:
            for match in re.finditer(pattern, content):
                full_text = match.group(0).strip()
                # Filter out HTML entities and code fragments
                if ('&#' not in full_text and 
                    'System.out' not in full_text and
                    len(full_text) > 30 and
                    len(full_text) < 150):
                    factual_sentences.append(full_text)
        
        logger.info(f"Found {len(factual_sentences)} factual sentences")
        
        # Also extract sentences mentioning ExecutorService concepts
        for sentence in content.split('.'):
            sentence = sentence.strip()
            if (50 < len(sentence) < 140 and
                any(term in sentence for term in ['ExecutorService', 'thread pool', 'executor', 'ThreadPool']) and
                '&#' not in sentence and
                'System.out' not in sentence and
                '()' not in sentence):
                factual_sentences.append(sentence + '.')
        
        # Deduplicate
        factual_sentences = list(set(factual_sentences))
        logger.info(f"Total unique sentences: {len(factual_sentences)}")
        
        # Create T/F questions
        for sentence in factual_sentences[:num]:
            questions.append({
                'question': sentence,
                'answer': True,
                'explanation': "This statement describes a key concept from the content"
            })
        
        # If not enough, add generic thread pool facts
        generic_facts = [
            ("Fixed thread pools maintain a constant number of threads", True),
            ("Cached thread pools create threads as needed", True),
            ("ExecutorService helps manage concurrent tasks", True),
        ]
        
        while len(questions) < num:
            fact, answer = generic_facts[len(questions) % len(generic_facts)]
            questions.append({
                'question': fact,
                'answer': answer,
                'explanation': "This is a general fact about thread pools"
            })
        
        logger.info(f"✅ Generated {len(questions)} T/F from factual sentences")
        return questions[:num]

# Initialize generator
generator = None

@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        'status': 'healthy',
        'service': 'Phi-3 SMART Fallback Mode',
        'model': 'microsoft/Phi-3-mini-4k-instruct',
        'quality': 'High AI + Intelligent concept extraction',
        'speed': '30-90 seconds (60s AI timeout, then smart fallback)'
    })

@app.route('/generate-quiz', methods=['POST'])
def generate_quiz():
    try:
        data = request.json
        content = data.get('content', '')
        num_questions = data.get('num_questions', 10)
        
        if not content:
            return jsonify({'success': False, 'error': 'No content provided'}), 400
        
        quiz_data = generator.generate_quiz(content, num_questions)
        
        return jsonify({
            'success': True,
            'quiz_data': quiz_data
        })
    
    except Exception as e:
        logger.error(f"Error: {e}")
        return jsonify({'success': False, 'error': str(e)}), 500

if __name__ == '__main__':
    print("\n" + "="*60)
    print("🧠 Phi-3 SMART FALLBACK Quiz Service")
    print("="*60)
    print("✅ AI-first: Tries Phi-3 for 60 seconds")
    print("✅ Smart fallback: Extracts COMPLETE concepts")
    print("✅ No more garbage: Uses full sentences, not single words")
    print("="*60 + "\n")
    
    generator = SmartPhi3QuizGenerator()
    
    print("\n🚀 Starting Flask server on http://127.0.0.1:5002")
    print("📊 Ready for quiz generation!\n")
    
    app.run(host='127.0.0.1', port=5002, debug=False)
