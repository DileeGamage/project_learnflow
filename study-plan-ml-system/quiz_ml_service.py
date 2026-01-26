"""
Quiz Generation ML Service

This service analyzes note content and generates intelligent quiz questions
using various ML techniques including:
- Named Entity Recognition (NER)
- Keyword extraction
- Text summarization 
- Question generation models
- Content analysis
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import json
import logging
import sys
import os
import re
import random
from typing import Dict, List, Any, Tuple

# ML/NLP Libraries
import nltk
import spacy
from transformers import pipeline, AutoTokenizer, AutoModelForSeq2SeqLM
from sklearn.feature_extraction.text import TfidfVectorizer
from keybert import KeyBERT
import textstat

# Download required NLTK data
try:
    nltk.download('punkt', quiet=True)
    nltk.download('stopwords', quiet=True)
    nltk.download('wordnet', quiet=True)
    nltk.download('averaged_perceptron_tagger', quiet=True)
except:
    pass

app = Flask(__name__)
CORS(app)

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class QuizGenerator:
    def __init__(self):
        """Initialize the quiz generation system"""
        logger.info("Initializing Quiz Generator...")
        
        # Load spaCy model
        try:
            self.nlp = spacy.load("en_core_web_sm")
        except OSError:
            logger.warning("spaCy model not found. Install with: python -m spacy download en_core_web_sm")
            self.nlp = None
        
        # Initialize keyword extraction
        self.kw_model = KeyBERT()
        
        # Initialize question generation pipeline (using T5)
        try:
            self.question_generator = pipeline(
                "text2text-generation",
                model="t5-small",
                tokenizer="t5-small"
            )
        except Exception as e:
            logger.warning(f"Question generation model not available: {e}")
            self.question_generator = None
        
        # NLTK components
        from nltk.corpus import stopwords
        from nltk.tokenize import sent_tokenize, word_tokenize
        from nltk.tag import pos_tag
        
        self.stop_words = set(stopwords.words('english'))
        self.sent_tokenize = sent_tokenize
        self.word_tokenize = word_tokenize
        self.pos_tag = pos_tag
        
        logger.info("Quiz Generator initialized successfully!")
    
    def analyze_content(self, content: str) -> Dict[str, Any]:
        """Analyze the content to understand its structure and key concepts"""
        
        # Clean content
        clean_content = self._clean_text(content)
        
        # Basic statistics
        word_count = len(clean_content.split())
        sentence_count = len(self.sent_tokenize(clean_content))
        reading_level = textstat.flesch_reading_ease(clean_content)
        
        # Extract keywords
        keywords = self._extract_keywords(clean_content)
        
        # Extract named entities
        entities = self._extract_entities(clean_content)
        
        # Identify key sentences
        key_sentences = self._identify_key_sentences(clean_content)
        
        # Detect subject area
        subject_area = self._detect_subject_area(clean_content, keywords)
        
        analysis = {
            'word_count': word_count,
            'sentence_count': sentence_count,
            'reading_level': reading_level,
            'keywords': keywords,
            'entities': entities,
            'key_sentences': key_sentences,
            'subject_area': subject_area,
            'difficulty_level': self._assess_difficulty(reading_level, word_count)
        }
        
        return analysis
    
    def generate_quiz(self, content: str, num_questions: int = 10, 
                     question_types: List[str] = None) -> Dict[str, Any]:
        """Generate a comprehensive quiz from the given content"""
        
        if question_types is None:
            question_types = ['multiple_choice', 'true_false', 'fill_blank', 'short_answer']
        
        # Analyze content first
        analysis = self.analyze_content(content)
        
        # Generate questions based on analysis
        questions = {
            'multiple_choice': [],
            'true_false': [],
            'fill_blank': [],
            'short_answer': []
        }
        
        # Calculate questions per type
        questions_per_type = max(1, num_questions // len(question_types))
        
        for q_type in question_types:
            if q_type == 'multiple_choice':
                questions['multiple_choice'] = self._generate_mcq(
                    content, analysis, min(questions_per_type, 6)
                )
            elif q_type == 'true_false':
                questions['true_false'] = self._generate_true_false(
                    content, analysis, min(questions_per_type, 4)
                )
            elif q_type == 'fill_blank':
                questions['fill_blank'] = self._generate_fill_blank(
                    content, analysis, min(questions_per_type, 3)
                )
            elif q_type == 'short_answer':
                questions['short_answer'] = self._generate_short_answer(
                    content, analysis, min(questions_per_type, 3)
                )
        
        # Compile final quiz
        quiz_data = {
            'quiz_id': f"quiz_{random.randint(1000, 9999)}",
            'content_analysis': analysis,
            'questions': questions,
            'total_questions': sum(len(q_list) for q_list in questions.values()),
            'estimated_time': self._estimate_completion_time(questions),
            'difficulty_level': analysis['difficulty_level']
        }
        
        return quiz_data
    
    def _clean_text(self, text: str) -> str:
        """Clean and preprocess text content"""
        # Remove HTML tags
        clean = re.sub(r'<[^>]+>', ' ', text)
        # Remove extra whitespace
        clean = re.sub(r'\s+', ' ', clean)
        # Remove special characters but keep basic punctuation
        clean = re.sub(r'[^\w\s.,;:!?()-]', ' ', clean)
        return clean.strip()
    
    def _extract_keywords(self, content: str, max_keywords: int = 15) -> List[Dict[str, Any]]:
        """Extract important keywords using KeyBERT"""
        try:
            keywords = self.kw_model.extract_keywords(
                content, 
                keyphrase_ngram_range=(1, 3), 
                stop_words='english',
                top_k=max_keywords
            )
            return [{'keyword': kw[0], 'score': float(kw[1])} for kw in keywords]
        except Exception as e:
            logger.warning(f"Keyword extraction failed: {e}")
            # Fallback: TF-IDF based extraction
            return self._extract_keywords_tfidf(content, max_keywords)
    
    def _extract_keywords_tfidf(self, content: str, max_keywords: int = 15) -> List[Dict[str, Any]]:
        """Fallback keyword extraction using TF-IDF"""
        try:
            vectorizer = TfidfVectorizer(
                max_features=max_keywords,
                stop_words='english',
                ngram_range=(1, 2)
            )
            tfidf_matrix = vectorizer.fit_transform([content])
            feature_names = vectorizer.get_feature_names_out()
            scores = tfidf_matrix.toarray()[0]
            
            keywords = []
            for i, score in enumerate(scores):
                if score > 0:
                    keywords.append({'keyword': feature_names[i], 'score': float(score)})
            
            return sorted(keywords, key=lambda x: x['score'], reverse=True)[:max_keywords]
        except Exception as e:
            logger.warning(f"TF-IDF keyword extraction failed: {e}")
            return []
    
    def _extract_entities(self, content: str) -> List[Dict[str, str]]:
        """Extract named entities from content"""
        entities = []
        
        if self.nlp:
            try:
                doc = self.nlp(content)
                for ent in doc.ents:
                    if len(ent.text.strip()) > 2:  # Filter out very short entities
                        entities.append({
                            'text': ent.text.strip(),
                            'label': ent.label_,
                            'description': spacy.explain(ent.label_) or ent.label_
                        })
            except Exception as e:
                logger.warning(f"Entity extraction failed: {e}")
        
        return entities[:20]  # Limit to top 20 entities
    
    def _identify_key_sentences(self, content: str, max_sentences: int = 10) -> List[str]:
        """Identify the most important sentences for question generation"""
        sentences = self.sent_tokenize(content)
        
        if len(sentences) <= max_sentences:
            return sentences
        
        # Score sentences based on keyword density and position
        sentence_scores = []
        
        for i, sentence in enumerate(sentences):
            score = 0
            words = self.word_tokenize(sentence.lower())
            
            # Position score (earlier sentences get higher scores)
            position_score = 1 - (i / len(sentences)) * 0.3
            score += position_score
            
            # Length score (prefer medium-length sentences)
            length_score = min(len(words) / 20, 1) if len(words) > 5 else 0
            score += length_score * 0.5
            
            # Keyword score (sentences with important terms)
            keyword_score = sum(1 for word in words if word not in self.stop_words) / len(words)
            score += keyword_score * 0.3
            
            sentence_scores.append((sentence, score))
        
        # Sort by score and return top sentences
        sentence_scores.sort(key=lambda x: x[1], reverse=True)
        return [sent[0] for sent in sentence_scores[:max_sentences]]
    
    def _detect_subject_area(self, content: str, keywords: List[Dict[str, Any]]) -> str:
        """Detect the subject area based on content and keywords"""
        
        # Subject area keywords
        subject_keywords = {
            'mathematics': ['equation', 'formula', 'calculate', 'theorem', 'proof', 'number', 'algebra', 'geometry'],
            'science': ['experiment', 'hypothesis', 'theory', 'research', 'analysis', 'method', 'data', 'observation'],
            'history': ['century', 'war', 'empire', 'revolution', 'ancient', 'medieval', 'modern', 'period'],
            'literature': ['author', 'novel', 'poem', 'character', 'plot', 'theme', 'symbolism', 'metaphor'],
            'computer_science': ['algorithm', 'programming', 'software', 'database', 'network', 'code', 'system'],
            'biology': ['cell', 'organism', 'evolution', 'genetics', 'ecosystem', 'species', 'protein', 'DNA'],
            'chemistry': ['molecule', 'atom', 'reaction', 'compound', 'element', 'bond', 'solution'],
            'physics': ['energy', 'force', 'motion', 'wave', 'particle', 'quantum', 'gravity', 'velocity']
        }
        
        content_lower = content.lower()
        keyword_text = ' '.join([kw['keyword'].lower() for kw in keywords])
        
        subject_scores = {}
        for subject, subject_words in subject_keywords.items():
            score = sum(1 for word in subject_words if word in content_lower or word in keyword_text)
            subject_scores[subject] = score
        
        if subject_scores:
            best_subject = max(subject_scores.items(), key=lambda x: x[1])
            return best_subject[0] if best_subject[1] > 0 else 'general'
        
        return 'general'
    
    def _assess_difficulty(self, reading_level: float, word_count: int) -> str:
        """Assess the difficulty level of the content"""
        if reading_level >= 70 and word_count < 500:
            return 'easy'
        elif reading_level >= 50 and word_count < 1000:
            return 'medium'
        else:
            return 'hard'
    
    def _generate_mcq(self, content: str, analysis: Dict[str, Any], num_questions: int) -> List[Dict[str, Any]]:
        """Generate multiple choice questions"""
        questions = []
        keywords = analysis['keywords'][:10]  # Top 10 keywords
        entities = analysis['entities'][:10]   # Top 10 entities
        key_sentences = analysis['key_sentences'][:num_questions*2]
        
        question_count = 0
        for sentence in key_sentences:
            if question_count >= num_questions:
                break
                
            # Try to create question from sentence
            question_data = self._create_mcq_from_sentence(sentence, keywords, entities)
            if question_data:
                questions.append(question_data)
                question_count += 1
        
        return questions
    
    def _create_mcq_from_sentence(self, sentence: str, keywords: List[Dict], entities: List[Dict]) -> Dict[str, Any]:
        """Create a multiple choice question from a sentence"""
        
        # Find important terms in the sentence
        words = self.word_tokenize(sentence)
        pos_tags = self.pos_tag(words)
        
        # Look for nouns, proper nouns, and adjectives that could be answer choices
        important_terms = []
        for word, pos in pos_tags:
            if pos in ['NN', 'NNS', 'NNP', 'NNPS', 'JJ'] and len(word) > 3:
                important_terms.append(word)
        
        # Also look for entities and keywords in the sentence
        sentence_lower = sentence.lower()
        for entity in entities:
            if entity['text'].lower() in sentence_lower and len(entity['text']) > 3:
                important_terms.append(entity['text'])
        
        for kw in keywords:
            if kw['keyword'].lower() in sentence_lower and len(kw['keyword']) > 3:
                important_terms.append(kw['keyword'])
        
        if not important_terms:
            return None
        
        # Select the best term as the correct answer
        correct_answer = max(important_terms, key=len)
        
        # Create question by replacing the term with a blank
        question_text = sentence.replace(correct_answer, "______", 1)
        question_text = f"What fills in the blank? {question_text}"
        
        # Generate distractors
        distractors = self._generate_distractors(correct_answer, important_terms, entities, keywords)
        
        # Create options
        options = [correct_answer] + distractors[:3]
        random.shuffle(options)
        
        # Find correct option index
        correct_index = options.index(correct_answer)
        correct_letter = chr(65 + correct_index)  # A, B, C, D
        
        return {
            'question': question_text,
            'options': [f"{chr(65+i)}) {opt}" for i, opt in enumerate(options)],
            'correct_answer': correct_letter,
            'explanation': f"The correct answer is {correct_answer}.",
            'difficulty': 'medium',
            'topic': 'content_analysis'
        }
    
    def _generate_distractors(self, correct_answer: str, terms: List[str], 
                            entities: List[Dict], keywords: List[Dict]) -> List[str]:
        """Generate plausible distractors for MCQ"""
        
        distractors = []
        
        # Use other terms from the same content
        for term in terms:
            if term != correct_answer and term not in distractors:
                distractors.append(term)
        
        # Use entities
        for entity in entities:
            if entity['text'] != correct_answer and entity['text'] not in distractors:
                distractors.append(entity['text'])
        
        # Use keywords
        for kw in keywords:
            if kw['keyword'] != correct_answer and kw['keyword'] not in distractors:
                distractors.append(kw['keyword'])
        
        # If not enough distractors, create some generic ones based on type
        while len(distractors) < 3:
            if len(correct_answer.split()) == 1:  # Single word
                distractors.extend(['None of the above', 'All of the above', 'Cannot be determined'])
            else:  # Multi-word
                distractors.extend(['Alternative explanation', 'Different approach', 'Not mentioned in text'])
            break
        
        return distractors[:3]
    
    def _generate_true_false(self, content: str, analysis: Dict[str, Any], num_questions: int) -> List[Dict[str, Any]]:
        """Generate true/false questions"""
        questions = []
        key_sentences = analysis['key_sentences'][:num_questions*2]
        
        question_count = 0
        for sentence in key_sentences:
            if question_count >= num_questions:
                break
            
            # Create true statement (original sentence simplified)
            true_question = self._create_true_false_question(sentence, True)
            if true_question:
                questions.append(true_question)
                question_count += 1
                
            if question_count >= num_questions:
                break
                
            # Create false statement (modified sentence)
            false_question = self._create_true_false_question(sentence, False)
            if false_question:
                questions.append(false_question)
                question_count += 1
        
        return questions[:num_questions]
    
    def _create_true_false_question(self, sentence: str, is_true: bool) -> Dict[str, Any]:
        """Create a true/false question from a sentence"""
        
        if is_true:
            # Use the sentence as-is or slightly simplified
            question_text = sentence.strip('.')
            answer = 'True'
            explanation = "This statement is directly supported by the content."
        else:
            # Modify the sentence to make it false
            modified_sentence = self._modify_sentence_for_false(sentence)
            if not modified_sentence or modified_sentence == sentence:
                return None
            question_text = modified_sentence.strip('.')
            answer = 'False'
            explanation = "This statement contradicts or misrepresents the content."
        
        return {
            'question': f"True or False: {question_text}",
            'correct_answer': answer,
            'explanation': explanation,
            'difficulty': 'easy',
            'topic': 'comprehension'
        }
    
    def _modify_sentence_for_false(self, sentence: str) -> str:
        """Modify a sentence to make it false"""
        
        # Simple modifications
        modifications = [
            ('is', 'is not'),
            ('are', 'are not'),
            ('was', 'was not'),
            ('were', 'were not'),
            ('can', 'cannot'),
            ('will', 'will not'),
            ('should', 'should not'),
            ('must', 'must not'),
            ('always', 'never'),
            ('never', 'always'),
            ('all', 'no'),
            ('every', 'no'),
            ('most', 'few'),
            ('many', 'few'),
            ('increase', 'decrease'),
            ('decrease', 'increase'),
            ('before', 'after'),
            ('after', 'before')
        ]
        
        sentence_lower = sentence.lower()
        for original, replacement in modifications:
            if f' {original} ' in sentence_lower:
                return sentence.replace(f' {original} ', f' {replacement} ', 1)
        
        return None
    
    def _generate_fill_blank(self, content: str, analysis: Dict[str, Any], num_questions: int) -> List[Dict[str, Any]]:
        """Generate fill-in-the-blank questions"""
        questions = []
        keywords = analysis['keywords'][:15]
        key_sentences = analysis['key_sentences'][:num_questions*2]
        
        question_count = 0
        for sentence in key_sentences:
            if question_count >= num_questions:
                break
                
            question_data = self._create_fill_blank_question(sentence, keywords)
            if question_data:
                questions.append(question_data)
                question_count += 1
        
        return questions
    
    def _create_fill_blank_question(self, sentence: str, keywords: List[Dict]) -> Dict[str, Any]:
        """Create a fill-in-the-blank question from a sentence"""
        
        # Find keywords in the sentence
        sentence_lower = sentence.lower()
        available_blanks = []
        
        for kw in keywords:
            keyword = kw['keyword']
            if keyword.lower() in sentence_lower and len(keyword) > 3:
                available_blanks.append(keyword)
        
        if not available_blanks:
            return None
        
        # Select the best keyword to blank out
        blank_word = max(available_blanks, key=len)
        
        # Create the question
        question_text = sentence.replace(blank_word, "________", 1)
        
        return {
            'question': f"Fill in the blank: {question_text}",
            'correct_answer': blank_word,
            'explanation': f"The correct answer is '{blank_word}' based on the content.",
            'difficulty': 'medium',
            'topic': 'recall',
            'answer_type': 'text'
        }
    
    def _generate_short_answer(self, content: str, analysis: Dict[str, Any], num_questions: int) -> List[Dict[str, Any]]:
        """Generate short answer questions"""
        questions = []
        keywords = analysis['keywords'][:10]
        entities = analysis['entities'][:10]
        
        # Generate different types of short answer questions
        question_templates = [
            "What is {concept}?",
            "Explain the significance of {concept}.",
            "How does {concept} relate to the main topic?",
            "What are the key characteristics of {concept}?",
            "Why is {concept} important in this context?"
        ]
        
        question_count = 0
        for kw in keywords:
            if question_count >= num_questions:
                break
                
            concept = kw['keyword']
            template = random.choice(question_templates)
            question_text = template.format(concept=concept)
            
            questions.append({
                'question': question_text,
                'suggested_answer': f"Based on the content, {concept} is an important concept that should be explained in 2-3 sentences.",
                'scoring_criteria': [
                    "Mentions key aspects of the concept",
                    "Uses information from the provided content", 
                    "Provides clear explanation"
                ],
                'difficulty': 'hard',
                'topic': 'analysis',
                'answer_type': 'paragraph'
            })
            question_count += 1
        
        return questions
    
    def _estimate_completion_time(self, questions: Dict[str, List]) -> int:
        """Estimate completion time in minutes"""
        time_per_question = {
            'multiple_choice': 1.5,
            'true_false': 1,
            'fill_blank': 2,
            'short_answer': 5
        }
        
        total_time = 0
        for q_type, q_list in questions.items():
            total_time += len(q_list) * time_per_question.get(q_type, 2)
        
        return max(5, int(total_time))  # Minimum 5 minutes

# Initialize quiz generator
quiz_gen = QuizGenerator()

@app.route('/', methods=['GET'])
def health_check():
    return jsonify({
        'status': 'healthy',
        'service': 'Quiz Generation ML Service',
        'version': '1.0.0'
    })

@app.route('/generate-quiz', methods=['POST'])
def generate_quiz():
    """Main endpoint for quiz generation"""
    
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
        
        # Generate quiz
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
        logger.error(f"Quiz generation error: {str(e)}")
        return jsonify({
            'success': False,
            'error': 'Failed to generate quiz. Please try again.'
        }), 500

@app.route('/analyze-content', methods=['POST'])
def analyze_content():
    """Endpoint for content analysis only"""
    
    try:
        data = request.json
        
        if not data or 'content' not in data:
            return jsonify({
                'success': False,
                'error': 'Content is required'
            }), 400
        
        content = data['content']
        
        if not content.strip():
            return jsonify({
                'success': False,
                'error': 'Content cannot be empty'
            }), 400
        
        # Analyze content
        analysis = quiz_gen.analyze_content(content)
        
        return jsonify({
            'success': True,
            'analysis': analysis
        })
        
    except Exception as e:
        logger.error(f"Content analysis error: {str(e)}")
        return jsonify({
            'success': False,
            'error': 'Failed to analyze content. Please try again.'
        }), 500

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5001)
