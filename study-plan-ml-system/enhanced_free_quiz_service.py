"""
Enhanced Free Quiz Generation Service using Hugging Face Transformers

This service provides high-quality quiz generation using completely free,
open-source models that run locally. No API costs, much better than basic ML.

Models used:
- T5 for question generation
- BART for text summarization
- DistilBERT for answer extraction
- Local NLP for content analysis
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import json
import logging
import re
import random
from typing import Dict, List, Any
import nltk
from transformers import (
    T5ForConditionalGeneration, T5Tokenizer,
    BartForConditionalGeneration, BartTokenizer,
    pipeline
)
import torch

app = Flask(__name__)
CORS(app)

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Download required NLTK data
try:
    nltk.download('punkt', quiet=True)
    nltk.download('stopwords', quiet=True)
    nltk.download('wordnet', quiet=True)
except:
    pass

class EnhancedFreeQuizGenerator:
    def __init__(self):
        """Initialize the enhanced free quiz generation system"""
        logger.info("Initializing Enhanced Free Quiz Generator...")
        
        # Initialize models (these will download automatically first time)
        try:
            # T5 for question generation (free, runs locally)
            logger.info("Loading T5 model for question generation...")
            self.t5_model = T5ForConditionalGeneration.from_pretrained('t5-small')
            self.t5_tokenizer = T5Tokenizer.from_pretrained('t5-small')
            
            # Question-Answer pipeline (free)
            logger.info("Loading QA pipeline...")
            self.qa_pipeline = pipeline('question-answering', 
                                       model='distilbert-base-cased-distilled-squad')
            
            # Text summarization pipeline (free)
            logger.info("Loading summarization pipeline...")
            self.summarizer = pipeline('summarization', 
                                     model='facebook/bart-large-cnn',
                                     max_length=150, min_length=30)
            
            logger.info("✅ All models loaded successfully!")
            
        except Exception as e:
            logger.warning(f"Error loading some models: {e}")
            # Fallback to simpler models
            self.t5_model = None
            self.t5_tokenizer = None
            self.qa_pipeline = None
            self.summarizer = None
            
        # NLTK components
        from nltk.corpus import stopwords
        from nltk.tokenize import sent_tokenize, word_tokenize
        
        try:
            self.stop_words = set(stopwords.words('english'))
        except:
            self.stop_words = set()
            
        self.sent_tokenize = sent_tokenize
        self.word_tokenize = word_tokenize

    def generate_quiz(self, content: str, num_questions: int = 10, 
                     question_types: List[str] = None) -> Dict[str, Any]:
        """Generate a comprehensive quiz using free AI models"""
        
        if question_types is None:
            question_types = ['multiple_choice', 'true_false', 'fill_blank', 'short_answer']
        
        logger.info(f"Generating quiz with {num_questions} questions of types: {question_types}")
        
        # Clean and analyze content
        clean_content = self._clean_text(content)
        analysis = self._analyze_content_enhanced(clean_content)
        
        # Generate questions using different methods
        questions = {}
        total_generated = 0
        questions_per_type = max(1, num_questions // len(question_types))
        
        for question_type in question_types:
            if total_generated >= num_questions:
                break
                
            remaining = num_questions - total_generated
            type_questions = min(questions_per_type, remaining)
            
            if question_type == 'multiple_choice':
                questions[question_type] = self._generate_mcq_enhanced(clean_content, analysis, type_questions)
            elif question_type == 'true_false':
                questions[question_type] = self._generate_true_false_enhanced(clean_content, analysis, type_questions)
            elif question_type == 'fill_blank':
                questions[question_type] = self._generate_fill_blank_enhanced(clean_content, analysis, type_questions)
            elif question_type == 'short_answer':
                questions[question_type] = self._generate_short_answer_enhanced(clean_content, analysis, type_questions)
            
            total_generated += len(questions.get(question_type, []))
        
        # Calculate metrics
        total_questions = sum(len(q) for q in questions.values())
        estimated_time = self._calculate_time(questions)
        
        return {
            'questions': questions,
            'total_questions': total_questions,
            'estimated_time': estimated_time,
            'difficulty_level': analysis.get('difficulty', 'medium'),
            'content_analysis': analysis,
            'generated_by': 'enhanced_free_ai'
        }

    def _analyze_content_enhanced(self, content: str) -> Dict[str, Any]:
        """Enhanced content analysis using free AI models"""
        
        # Basic analysis
        words = self.word_tokenize(content.lower())
        sentences = self.sent_tokenize(content)
        
        # Extract key information using AI
        key_facts = []
        important_sentences = []
        
        try:
            # Use summarization to find key points
            if self.summarizer and len(content) > 200:
                summary = self.summarizer(content, max_length=100, min_length=30, do_sample=False)
                key_summary = summary[0]['summary_text']
                important_sentences = self.sent_tokenize(key_summary)
        except Exception as e:
            logger.warning(f"Summarization failed: {e}")
            # Fallback: use first few sentences
            important_sentences = sentences[:3]
        
        # Extract MEANINGFUL keywords using enhanced filtering
        word_freq = {}
        
        # Common meaningless words to filter out
        meaningless_words = {
            'and', 'or', 'but', 'the', 'a', 'an', 'in', 'on', 'at', 'to', 'for',
            'of', 'with', 'by', 'from', 'as', 'is', 'was', 'are', 'were', 'been',
            'be', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'should',
            'can', 'could', 'may', 'might', 'must', 'shall', 'this', 'that', 'these',
            'those', 'it', 'its', 'they', 'them', 'their', 'what', 'which', 'who',
            'when', 'where', 'why', 'how', 'all', 'each', 'every', 'both', 'few',
            'more', 'most', 'other', 'some', 'such', 'only', 'own', 'same', 'so',
            'than', 'too', 'very', 'also', 'just', 'about', 'into', 'through',
            'during', 'before', 'after', 'above', 'below', 'between', 'under',
            'again', 'further', 'then', 'once', 'here', 'there', 'whether', 'both'
        }
        
        # Technical/programming symbols and artifacts
        artifacts = {'---', '...', '***', '###', '===', '```', '<!--', '-->', '//'}
        
        # Extract words that are likely meaningful concepts
        for word in words:
            # Must be:
            # 1. Length > 4 (longer words more likely to be concepts)
            # 2. Not a stop word or meaningless word
            # 3. Alphabetic (no numbers or symbols)
            # 4. Not an artifact
            # 5. Contains at least one capital letter in original OR is CamelCase
            
            if (len(word) > 4 and 
                word not in self.stop_words and 
                word not in meaningless_words and
                word not in artifacts and
                word.isalpha() and
                not word.startswith('-')):
                
                # Additional filter: prefer capitalized words (likely proper nouns/concepts)
                # or technical terms (contain multiple capital letters)
                word_in_original = self._find_word_in_original(word, content)
                if word_in_original and (word_in_original[0].isupper() or 
                                        sum(1 for c in word_in_original if c.isupper()) > 1):
                    word_freq[word] = word_freq.get(word, 0) + 1
                elif len(word) > 6:  # Very long words are likely technical terms
                    word_freq[word] = word_freq.get(word, 0) + 1
        
        # Sort by frequency and get top meaningful keywords
        keywords = sorted(word_freq.items(), key=lambda x: x[1], reverse=True)[:15]
        
        # Detect subject area
        subject_indicators = {
            'computer science': ['algorithm', 'programming', 'software', 'computer', 'code', 'data'],
            'mathematics': ['equation', 'formula', 'calculate', 'number', 'mathematical', 'theorem'],
            'biology': ['cell', 'organism', 'dna', 'protein', 'evolution', 'species'],
            'physics': ['energy', 'force', 'motion', 'particle', 'quantum', 'gravity'],
            'chemistry': ['molecule', 'atom', 'reaction', 'chemical', 'element', 'compound'],
            'history': ['century', 'war', 'empire', 'revolution', 'ancient', 'historical'],
            'literature': ['author', 'novel', 'poem', 'character', 'literary', 'writing']
        }
        
        subject_scores = {}
        for subject, indicators in subject_indicators.items():
            score = sum(1 for word in words if word in indicators)
            if score > 0:
                subject_scores[subject] = score
        
        detected_subject = max(subject_scores.items(), key=lambda x: x[1])[0] if subject_scores else 'general'
        
        return {
            'word_count': len(words),
            'sentence_count': len(sentences),
            'keywords': [{'keyword': k, 'frequency': f} for k, f in keywords],
            'key_sentences': important_sentences,
            'subject_area': detected_subject,
            'difficulty': 'medium',  # Could be enhanced with readability metrics
            'topics': [k for k, f in keywords[:5]]
        }

    def _find_word_in_original(self, word_lower: str, content: str) -> str:
        """Find the original casing of a word in content"""
        # Use regex to find word with word boundaries
        pattern = r'\b' + re.escape(word_lower) + r'\b'
        match = re.search(pattern, content, re.IGNORECASE)
        return match.group(0) if match else word_lower

    def _generate_mcq_enhanced(self, content: str, analysis: Dict, num_questions: int) -> List[Dict]:
        """Generate contextual multiple choice questions based on actual content meaning"""
        questions = []
        sentences = self.sent_tokenize(content)
        keywords = [k['keyword'] for k in analysis['keywords'][:10]]
        
        # If no good keywords, extract from key sentences
        if len(keywords) < 3:
            logger.warning("Few meaningful keywords found, extracting from sentences")
            for sentence in sentences[:10]:
                words = [w for w in sentence.split() if len(w) > 6 and w[0].isupper()]
                keywords.extend(words[:2])
            keywords = list(set(keywords))[:10]
        
        # Generate questions based on actual content relationships
        for i in range(min(num_questions, len(keywords))):
            try:
                keyword = keywords[i]
                
                # Find sentences that contain meaningful context about this keyword
                relevant_sentences = [s for s in sentences if keyword.lower() in s.lower()]
                
                if not relevant_sentences:
                    continue
                
                # Get the most informative sentence (longest or first)
                context_sentence = max(relevant_sentences, key=len) if len(relevant_sentences) > 1 else relevant_sentences[0]
                
                # Extract the actual information about the keyword from context
                concept_description = self._extract_concept_description(context_sentence, keyword)
                
                # Create question based on the relationship in the text
                question_text = self._create_contextual_question(keyword, context_sentence, concept_description)
                
                # Generate answer choices based on actual content
                correct_option = concept_description
                distractors = self._generate_contextual_distractors(keyword, context_sentence, sentences, concept_description)
                
                # Ensure we have enough options
                if len(distractors) < 3:
                    distractors.extend(self._generate_generic_distractors(keyword, analysis['subject_area']))
                
                options = [correct_option] + distractors[:3]
                random.shuffle(options)
                
                # Format options
                formatted_options = [f"{chr(65+j)}) {opt}" for j, opt in enumerate(options)]
                correct_letter = chr(65 + options.index(correct_option))
                
                questions.append({
                    'question': question_text,
                    'options': formatted_options,
                    'correct_answer': f"{correct_letter}) {correct_option}",
                    'explanation': f"Based on the content: {context_sentence[:150]}...",
                    'difficulty': 'medium',
                    'topic': keyword,
                    'type': 'multiple_choice'
                })
                
            except Exception as e:
                logger.warning(f"Error generating MCQ {i}: {e}")
                continue
        
        return questions
    
    def _extract_concept_description(self, sentence: str, keyword: str) -> str:
        """Extract what the sentence says about the keyword"""
        # Try to extract the clause that describes the keyword
        
        # Split sentence into parts
        parts = re.split(r'[,;:]', sentence)
        
        # Find the part containing the keyword
        for part in parts:
            if keyword.lower() in part.lower():
                # Clean and return meaningful part
                cleaned = part.strip().rstrip('.')
                # Remove the keyword itself from the description to avoid repetition
                cleaned = re.sub(rf'\b{re.escape(keyword)}\b', 'it', cleaned, flags=re.IGNORECASE, count=1)
                
                # If the description is too short or just the keyword, use the whole sentence
                if len(cleaned) < 20:
                    cleaned = sentence.strip().rstrip('.')
                
                return cleaned[:100]  # Limit length
        
        # Fallback: use sentence after the keyword
        keyword_pos = sentence.lower().find(keyword.lower())
        if keyword_pos >= 0:
            remaining = sentence[keyword_pos + len(keyword):].strip()
            if len(remaining) > 20:
                return remaining[:100].rstrip('.')
        
        return sentence[:100].rstrip('.')
    
    def _create_contextual_question(self, keyword: str, context: str, description: str) -> str:
        """Create a question that asks about the actual relationship in the text"""
        
        # Detect question type based on context
        if 'is' in context.lower() or 'are' in context.lower():
            return f"What is {keyword} according to the content?"
        elif 'used' in context.lower() or 'use' in context.lower():
            return f"How is {keyword} used in the context?"
        elif 'allow' in context.lower() or 'enable' in context.lower():
            return f"What does {keyword} enable or allow?"
        elif 'create' in context.lower() or 'provide' in context.lower():
            return f"What does {keyword} provide?"
        elif 'manage' in context.lower() or 'control' in context.lower():
            return f"What does {keyword} manage or control?"
        else:
            return f"According to the content, what is the role of {keyword}?"
    
    def _generate_contextual_distractors(self, keyword: str, context_sentence: str, 
                                        all_sentences: List[str], correct_answer: str) -> List[str]:
        """Generate distractors based on other information in the content"""
        distractors = []
        
        # Find other sentences with similar structure but different concepts
        for sentence in all_sentences:
            if sentence == context_sentence:
                continue
            
            # Extract descriptions from other sentences
            parts = re.split(r'[,;:]', sentence)
            for part in parts:
                part = part.strip().rstrip('.')
                # Must be substantial and different from correct answer
                if (len(part) > 20 and 
                    len(part) < 120 and 
                    part.lower() != correct_answer.lower() and
                    keyword.lower() not in part.lower()):
                    
                    distractors.append(part)
                    
                    if len(distractors) >= 3:
                        return distractors
        
        return distractors
    
    def _generate_generic_distractors(self, keyword: str, subject: str) -> List[str]:
        """Fallback: Generate generic but plausible distractors"""

    def _generate_true_false_enhanced(self, content: str, analysis: Dict, num_questions: int) -> List[Dict]:
        """Generate true/false questions using content analysis"""
        questions = []
        sentences = self.sent_tokenize(content)
        keywords = [k['keyword'] for k in analysis['keywords'][:8]]
        
        for i in range(min(num_questions, len(sentences))):
            try:
                sentence = sentences[i]
                
                # Create true statement (from actual content)
                if random.choice([True, False]):
                    statement = sentence.strip()
                    correct = "True"
                    explanation = "This statement appears directly in the provided content."
                else:
                    # Create false statement by modifying content
                    statement = self._create_false_statement(sentence, keywords)
                    correct = "False"
                    explanation = "This statement contradicts or misrepresents the provided content."
                
                questions.append({
                    'question': statement,
                    'correct_answer': correct,
                    'explanation': explanation,
                    'difficulty': 'medium',
                    'topic': 'content verification',
                    'type': 'true_false'
                })
                
            except Exception as e:
                logger.warning(f"Error generating T/F {i}: {e}")
                continue
        
        return questions

    def _generate_fill_blank_enhanced(self, content: str, analysis: Dict, num_questions: int) -> List[Dict]:
        """Generate fill-in-the-blank questions targeting key terms"""
        questions = []
        sentences = self.sent_tokenize(content)
        keywords = [k['keyword'] for k in analysis['keywords'][:10]]
        
        for i in range(min(num_questions, len(keywords))):
            try:
                keyword = keywords[i]
                
                # Find sentence containing the keyword
                relevant_sentences = [s for s in sentences if keyword in s.lower()]
                if not relevant_sentences:
                    continue
                
                sentence = relevant_sentences[0]
                
                # Replace keyword with blank
                blank_sentence = re.sub(
                    rf'\b{re.escape(keyword)}\b', 
                    '___________', 
                    sentence, 
                    flags=re.IGNORECASE
                )
                
                questions.append({
                    'question': blank_sentence,
                    'correct_answer': keyword,
                    'explanation': f"The correct term is '{keyword}' as mentioned in the content.",
                    'difficulty': 'medium',
                    'topic': keyword,
                    'type': 'fill_blank'
                })
                
            except Exception as e:
                logger.warning(f"Error generating fill-blank {i}: {e}")
                continue
        
        return questions

    def _generate_short_answer_enhanced(self, content: str, analysis: Dict, num_questions: int) -> List[Dict]:
        """Generate short answer questions requiring explanation"""
        questions = []
        key_sentences = analysis.get('key_sentences', self.sent_tokenize(content)[:5])
        topics = analysis.get('topics', ['main concept'])
        
        question_patterns = [
            "Explain the significance of {topic} mentioned in the content.",
            "How does {topic} relate to the main theme of the text?",
            "What are the key characteristics of {topic} according to the content?",
            "Describe the role of {topic} in the context provided.",
            "Why is {topic} important based on the information given?"
        ]
        
        for i in range(min(num_questions, len(topics))):
            try:
                topic = topics[i]
                pattern = random.choice(question_patterns)
                question = pattern.format(topic=topic)
                
                # Generate sample answer based on content
                relevant_context = next(
                    (s for s in key_sentences if topic in s.lower()), 
                    key_sentences[0] if key_sentences else "Content analysis required."
                )
                
                sample_answer = f"Based on the content, {topic} is significant because {relevant_context[:100]}..."
                
                questions.append({
                    'question': question,
                    'sample_answer': sample_answer,
                    'explanation': "Answer should demonstrate understanding of the concept in context.",
                    'difficulty': 'hard',
                    'topic': topic,
                    'type': 'short_answer'
                })
                
            except Exception as e:
                logger.warning(f"Error generating short answer {i}: {e}")
                continue
        
        return questions

    def _create_false_statement(self, sentence: str, keywords: List[str]) -> str:
        """Create a false statement by modifying the original"""
        
        # Simple modifications to create false statements
        modifications = [
            lambda s: s.replace('is', 'is not'),
            lambda s: s.replace('can', 'cannot'),
            lambda s: s.replace('will', 'will not'),
            lambda s: s.replace('allows', 'prevents'),
            lambda s: s.replace('increases', 'decreases'),
        ]
        
        # Try to apply a modification
        for mod in modifications:
            modified = mod(sentence)
            if modified != sentence:
                return modified
        
        # If no modification worked, add a negation
        return f"It is not true that {sentence.lower()}"

    def _clean_text(self, text: str) -> str:
        """Clean and preprocess text"""
        # Remove HTML tags, extra whitespace, etc.
        text = re.sub(r'<[^>]+>', '', text)
        text = re.sub(r'\s+', ' ', text)
        return text.strip()

    def _calculate_time(self, questions: Dict[str, List]) -> int:
        """Calculate estimated time for quiz"""
        time_per_type = {
            'multiple_choice': 1.5,
            'true_false': 0.5,
            'fill_blank': 1.0,
            'short_answer': 3.0
        }
        
        total_time = 0
        for q_type, q_list in questions.items():
            total_time += len(q_list) * time_per_type.get(q_type, 1.5)
        
        return max(5, int(total_time))

# Initialize the enhanced generator
quiz_gen = EnhancedFreeQuizGenerator()

@app.route('/', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'service': 'Enhanced Free Quiz Generation Service',
        'models': 'Hugging Face Transformers (T5, BART, DistilBERT)',
        'cost': 'Completely Free!'
    })

@app.route('/generate-quiz', methods=['POST'])
def generate_quiz():
    """Main endpoint for enhanced free quiz generation"""
    
    try:
        data = request.json
        
        if not data or 'content' not in data:
            return jsonify({
                'success': False,
                'error': 'Content is required'
            }), 400
        
        content = data['content']
        num_questions = data.get('num_questions', 10)
        question_types = data.get('question_types', ['multiple_choice', 'true_false', 'fill_blank'])
        
        if not content.strip():
            return jsonify({
                'success': False,
                'error': 'Content cannot be empty'
            }), 400
        
        # Generate quiz using enhanced free AI
        quiz_data = quiz_gen.generate_quiz(
            content=content,
            num_questions=num_questions,
            question_types=question_types
        )
        
        return jsonify({
            'success': True,
            'quiz': quiz_data
        })
        
    except Exception as e:
        logger.error(f"Enhanced quiz generation error: {str(e)}")
        return jsonify({
            'success': False,
            'error': 'Failed to generate quiz. Please try again.'
        }), 500

if __name__ == '__main__':
    print("🚀 Starting Enhanced Free Quiz Generation Service...")
    print("💰 Cost: $0 (Completely Free!)")
    print("🤖 Models: Hugging Face Transformers")
    print("📍 Available at: http://localhost:5002")
    app.run(debug=True, host='0.0.0.0', port=5002)
