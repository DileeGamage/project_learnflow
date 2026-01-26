"""
HYBRID Quiz Generation Service - FAST & RELIABLE

Strategy:
1. Extract key concepts from content (regex-based, instant)
2. Generate questions using templates + smart variations
3. NO slow AI generation - pure speed

Speed: < 1 second per quiz
Quality: Good (focused on actual technical content)
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import json
import logging
import re
import random
from typing import Dict, List, Tuple

app = Flask(__name__)
CORS(app)

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class HybridQuizGenerator:
    def __init__(self):
        """Initialize hybrid generator (no model loading needed)"""
        logger.info("=" * 70)
        logger.info("⚡ HYBRID Quiz Generator - INSTANT SPEED")
        logger.info("=" * 70)
        logger.info("🎯 Method: Smart templates + concept extraction")
        logger.info("⚡ Speed: < 1 second per quiz")
        logger.info("💰 Cost: $0")
        logger.info("=" * 70)
        
        # Question templates
        self.mcq_templates = [
            "What is the main purpose of {concept}?",
            "Which statement best describes {concept}?",
            "What is a key advantage of using {concept}?",
            "When should you use {concept}?",
            "What problem does {concept} solve?",
            "How does {concept} improve {related_concept}?",
        ]
        
        self.tf_templates = [
            "{concept} provides {benefit}",
            "{concept} is used for {purpose}",
            "{concept} requires {requirement}",
            "{concept} offers better {feature} than {alternative}",
        ]
        
        logger.info("✅ Generator ready - NO model loading required!")
        logger.info("=" * 70)
    
    def generate_quiz(self, content: str, num_questions: int = 10) -> dict:
        """Generate quiz INSTANTLY using hybrid approach"""
        
        logger.info(f"📝 Processing: {len(content)} chars, {num_questions} questions")
        
        if len(content) < 100:
            return {'multiple_choice': [], 'true_false': []}
        
        # Extract concepts
        concepts = self._extract_concepts(content)
        sentences = self._extract_sentences(content)
        
        logger.info(f"📚 Found {len(concepts)} concepts, {len(sentences)} sentences")
        
        if not concepts or not sentences:
            logger.warning("No concepts found, returning empty quiz")
            return {'multiple_choice': [], 'true_false': []}
        
        # Calculate distribution
        num_tf = min(3, int(num_questions * 0.3))
        num_mcq = num_questions - num_tf
        
        quiz_data = {
            'true_false': [],
            'multiple_choice': []
        }
        
        # Generate T/F questions
        logger.info(f"🟢 Generating {num_tf} T/F questions...")
        for i in range(num_tf):
            q = self._generate_tf_hybrid(concepts, sentences, i)
            if q:
                quiz_data['true_false'].append(q)
        
        # Generate MCQ questions
        logger.info(f"🔵 Generating {num_mcq} MCQ questions...")
        for i in range(num_mcq):
            q = self._generate_mcq_hybrid(concepts, sentences, i)
            if q:
                quiz_data['multiple_choice'].append(q)
        
        total = len(quiz_data['true_false']) + len(quiz_data['multiple_choice'])
        logger.info(f"✅ Generated {total} questions INSTANTLY")
        
        return quiz_data
    
    def _extract_concepts(self, content: str) -> List[str]:
        """Extract key technical concepts and educational topics"""
        # Priority 1: Find well-known technical terms (APIs, frameworks, patterns)
        priority_patterns = [
            r'\b(?:ExecutorService|ThreadPool|ScheduledExecutorService|Callable|Runnable|Future|CompletableFuture)\b',
            r'\b(?:newFixedThreadPool|newCachedThreadPool|newSingleThreadExecutor|newScheduledThreadPool)\b',
            r'\b(?:shutdown|shutdownNow|awaitTermination|submit|execute|invokeAll)\b',
        ]
        
        concepts = []
        for pattern in priority_patterns:
            found = re.findall(pattern, content, re.IGNORECASE)
            concepts.extend([f.lower() for f in found])
        
        # Priority 2: Technical terms with common suffixes
        if len(concepts) < 5:
            tech_patterns = [
                r'\b[A-Z][a-zA-Z]{3,}(?:Service|Pool|Executor|Thread|Pattern|Manager|Interface|Framework)\b',
            ]
            for pattern in tech_patterns:
                found = re.findall(pattern, content)
                concepts.extend(found)
        
        # Remove bad concepts (code fragments, variables)
        exclude = ['Page', 'The', 'This', 'That', 'Chapter', 'Section', 'Content', 'Information', 
                   'First', 'Second', 'Third', 'Example', 'Following', 'Previous', 'Next',
                   'Simulate', 'System', 'String', 'Integer', 'Boolean', 'Main', 'Args',
                   'PhotoUploadService', 'BookingConsider', 'InterrupteSystem', 'Always']
        
        # Filter out anything that looks like code (has underscores, all caps, etc)
        concepts = [c for c in concepts if c not in exclude 
                    and len(c) > 3 
                    and not '_' in c 
                    and not c.isupper()
                    and not any(char.isdigit() for char in c)]
        
        return list(set(concepts))[:20]
    
    def _extract_sentences(self, content: str) -> List[str]:
        """Extract meaningful educational sentences"""
        # Split by period, filter short and bad sentences
        sentences = re.split(r'[.!?]+', content)
        sentences = [s.strip() for s in sentences if 30 < len(s) < 200]
        
        # Filter out bad sentences
        bad_patterns = [
            r'page \d+',
            r'chapter \d+',
            r'---',
            r'^\s*\d+\s*$',  # Just numbers
            r'public static void',  # Code lines
            r'^\s*\w+\s*\(',  # Function calls
            r'System\.out',  # Print statements
            r'\.printStackTrace',  # Stack traces
            r'^\s*//\s*',  # Comments
            r'Thread\.sleep\(\d+\)',  # Code fragments
        ]
        
        filtered_sentences = []
        for s in sentences:
            # Skip if matches any bad pattern
            if any(re.search(pattern, s, re.IGNORECASE) for pattern in bad_patterns):
                continue
            # Must contain actual words (not just code)
            if len(re.findall(r'\b[a-zA-Z]{4,}\b', s)) >= 5:
                filtered_sentences.append(s)
        
        return filtered_sentences[:30]
    
    def _generate_tf_hybrid(self, concepts: List[str], sentences: List[str], num: int) -> dict:
        """Generate T/F question from actual content"""
        if not sentences:
            return None
        
        # Pick a random sentence that contains a concept
        valid_sentences = [s for s in sentences if any(c in s for c in concepts)]
        if not valid_sentences:
            valid_sentences = sentences
        
        if not valid_sentences:
            return None
        
        sentence = random.choice(valid_sentences)
        
        # Clean up the sentence
        sentence = re.sub(r'\s+', ' ', sentence).strip()
        
        # Determine if this should be true or false
        is_true = random.choice([True, False])
        
        if not is_true:
            # Modify the sentence to make it false
            # Simple modifications
            if 'provides' in sentence.lower():
                sentence = sentence.replace('provides', 'does not provide')
    def _generate_mcq_hybrid(self, concepts: List[str], sentences: List[str], num: int) -> dict:
        """Generate MCQ question from actual content"""
        if not sentences:
            return None
        
        # Find a good educational sentence
        sentence = sentences[num % len(sentences)]
        
        # Extract the main topic/concept from the sentence
        concept_match = re.search(r'\b(executor|thread|pool|task|callable|runnable|shutdown|schedule|fixed|cached|single)\w*\b', sentence, re.IGNORECASE)
        concept = concept_match.group(0) if concept_match else "this feature"
        
        # Create question templates based on common educational patterns
        templates = [
            f"What is the main advantage of using {concept}?",
            f"Which method is used to {concept.lower()}?",
            f"What happens when you use {concept}?",
            f"Which type of pool is best for {concept.lower()}?",
        ]
        
        question = random.choice(templates)
        
        # Extract a meaningful phrase from the sentence as the correct answer
        # Look for phrases with verbs + objects
        verb_phrases = re.findall(r'(provides?|offers?|enables?|allows?|handles?|manages?|supports?|implements?|executes?|creates?|reuses?)\s+[\w\s]{10,60}', sentence, re.IGNORECASE)
        
        if verb_phrases:
            correct_answer = verb_phrases[0].strip()
            # Clean up the answer
            correct_answer = re.sub(r'\s+', ' ', correct_answer)
            if len(correct_answer) > 80:
                correct_answer = ' '.join(correct_answer.split()[:12])
        else:
            # Fall back to middle portion of sentence
            words = sentence.split()
            if len(words) > 10:
                correct_answer = ' '.join(words[3:10])
            else:
                return None  # Skip this question if can't extract good answer
        
        # Generate relevant distractors (not generic ones)
        thread_distractors = [
            "It creates new threads for every task and destroys them after completion",
            "It executes tasks in random order with no guarantees",
            "It requires manual thread management and synchronization",
            "It blocks all other tasks until the current one completes",
            "It only works with single-threaded applications",
            "It automatically prevents all deadlocks and race conditions",
            "It guarantees faster execution than manual threads in all cases",
            "It removes the need for any synchronization mechanisms"
        ]
        
        # Pick 3 different distractors
        wrong_answers = random.sample(thread_distractors, 3)
        
        # Combine and shuffle
        all_options = [correct_answer] + wrong_answers
        random.shuffle(all_options)
        
        # Find correct letter
        correct_letter = chr(65 + all_options.index(correct_answer))
        
        options_dict = {
            'A': all_options[0],
            'B': all_options[1],
            'C': all_options[2],
            'D': all_options[3]
        }
        
        return {
            'question': question,
            'options': options_dict,
            'correct_answer': correct_letter,
            'explanation': f"The correct answer is based on how {concept} works in the content."
        }
        # Combine all options
        all_options = [correct_answer] + wrong_answers
        random.shuffle(all_options)
        
        # Find correct letter
        correct_letter = chr(65 + all_options.index(correct_answer))  # A=65
        
        options_dict = {
            'A': all_options[0],
            'B': all_options[1],
            'C': all_options[2],
            'D': all_options[3]
        }
        
        return {
            'question': question,
            'options': options_dict,
            'correct_answer': correct_letter,
            'explanation': f"Based on the content, {concept} {correct_answer.lower()}."
        }

# Global generator instance
generator = HybridQuizGenerator()

@app.route('/health', methods=['GET'])
def health():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'service': 'Hybrid Quiz Generator (INSTANT)',
        'mode': 'template-based-with-concept-extraction',
        'model_info': {
            'name': 'Smart Templates',
            'speed': '< 1 second',
            'quality': 'Good (content-based)'
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
    logger.info("⚡ HYBRID Quiz Generation Service")
    logger.info("=" * 80)
    logger.info("🚀 INSTANT generation - no AI model loading!")
    logger.info("🎯 Smart templates + concept extraction")
    logger.info("⚡ Speed: < 1 second per quiz")
    logger.info("💰 Cost: $0 (no model, no API)")
    logger.info("=" * 80)
    
    # Start Flask app
    app.run(host='0.0.0.0', port=5002, debug=False)
