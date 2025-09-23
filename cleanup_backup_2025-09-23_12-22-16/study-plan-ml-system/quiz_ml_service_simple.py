"""
Simple Quiz Generation Service (Basic Version)

A lightweight quiz generation service that doesn't require heavy ML dependencies.
This version uses basic text processing to generate questions.
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
import string

app = Flask(__name__)
CORS(app)

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class SimpleQuizGenerator:
    def __init__(self):
        """Initialize the simple quiz generation system"""
        logger.info("Initializing Simple Quiz Generator...")
        
        # Common stop words
        self.stop_words = set([
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with',
            'by', 'from', 'up', 'about', 'into', 'through', 'during', 'before', 'after',
            'above', 'below', 'out', 'off', 'over', 'under', 'again', 'further', 'then',
            'once', 'here', 'there', 'when', 'where', 'why', 'how', 'all', 'any', 'both',
            'each', 'few', 'more', 'most', 'other', 'some', 'such', 'no', 'nor', 'not',
            'only', 'own', 'same', 'so', 'than', 'too', 'very', 's', 't', 'can', 'will',
            'just', 'don', "don't", 'should', "should've", 'now', 'd', 'll', 'm', 'o', 're',
            've', 'y', 'ain', 'aren', "aren't", 'couldn', "couldn't", 'didn', "didn't",
            'doesn', "doesn't", 'hadn', "hadn't", 'hasn', "hasn't", 'haven', "haven't",
            'isn', "isn't", 'ma', 'mightn', "mightn't", 'mustn', "mustn't", 'needn',
            "needn't", 'shan', "shan't", 'shouldn', "shouldn't", 'wasn', "wasn't",
            'weren', "weren't", 'won', "won't", 'wouldn', "wouldn't"
        ])
        
        logger.info("Simple Quiz Generator initialized successfully!")
    
    def clean_text(self, text: str) -> str:
        """Clean and preprocess text content"""
        # Remove HTML tags
        clean = re.sub(r'<[^>]+>', ' ', text)
        # Remove extra whitespace
        clean = re.sub(r'\s+', ' ', clean)
        # Remove special characters but keep basic punctuation
        clean = re.sub(r'[^\w\s.,;:!?()-]', ' ', clean)
        return clean.strip()
    
    def tokenize_sentences(self, text: str) -> List[str]:
        """Simple sentence tokenization"""
        # Split on sentence endings
        sentences = re.split(r'[.!?]+', text)
        # Clean and filter sentences
        cleaned_sentences = []
        for sentence in sentences:
            sentence = sentence.strip()
            if len(sentence) > 20:  # Only keep substantial sentences
                cleaned_sentences.append(sentence)
        return cleaned_sentences
    
    def tokenize_words(self, text: str) -> List[str]:
        """Simple word tokenization"""
        # Convert to lowercase and split on non-word characters
        words = re.findall(r'\b\w+\b', text.lower())
        return words
    
    def extract_keywords(self, text: str, max_keywords: int = 15) -> List[Dict[str, Any]]:
        """Extract important keywords using simple frequency analysis"""
        words = self.tokenize_words(text)
        
        # Filter out stop words and short words
        filtered_words = [word for word in words if word not in self.stop_words and len(word) > 3]
        
        # Count word frequencies
        word_freq = {}
        for word in filtered_words:
            word_freq[word] = word_freq.get(word, 0) + 1
        
        # Sort by frequency and return top keywords
        sorted_words = sorted(word_freq.items(), key=lambda x: x[1], reverse=True)
        
        keywords = []
        for word, freq in sorted_words[:max_keywords]:
            score = freq / len(filtered_words)  # Normalize frequency
            keywords.append({'keyword': word, 'score': score})
        
        return keywords
    
    def extract_entities(self, text: str) -> List[Dict[str, str]]:
        """Extract potential named entities using simple rules"""
        entities = []
        
        # Find capitalized words (potential proper nouns)
        words = re.findall(r'\b[A-Z][a-z]+\b', text)
        
        # Find potential dates
        dates = re.findall(r'\b\d{4}\b|\b\d{1,2}/\d{1,2}/\d{2,4}\b', text)
        
        # Find numbers
        numbers = re.findall(r'\b\d+\.?\d*\b', text)
        
        # Add capitalized words as potential entities
        for word in set(words):
            if len(word) > 2 and word.lower() not in self.stop_words:
                entities.append({
                    'text': word,
                    'label': 'PERSON_OR_PLACE',
                    'description': 'Potential person or place name'
                })
        
        # Add dates
        for date in set(dates):
            entities.append({
                'text': date,
                'label': 'DATE',
                'description': 'Date or year'
            })
        
        # Add significant numbers
        for num in set(numbers):
            if len(num) > 1:  # Skip single digits
                entities.append({
                    'text': num,
                    'label': 'NUMBER',
                    'description': 'Numerical value'
                })
        
        return entities[:20]  # Limit to top 20
    
    def analyze_content(self, content: str) -> Dict[str, Any]:
        """Analyze the content to understand its structure and key concepts"""
        
        # Clean content
        clean_content = self.clean_text(content)
        
        # Basic statistics
        words = self.tokenize_words(clean_content)
        sentences = self.tokenize_sentences(clean_content)
        
        word_count = len(words)
        sentence_count = len(sentences)
        
        # Simple reading level (based on average sentence length)
        avg_sentence_length = word_count / max(sentence_count, 1)
        if avg_sentence_length < 15:
            reading_level = 80  # Easy
        elif avg_sentence_length < 25:
            reading_level = 60  # Medium
        else:
            reading_level = 40  # Hard
        
        # Extract keywords
        keywords = self.extract_keywords(clean_content)
        
        # Extract entities
        entities = self.extract_entities(clean_content)
        
        # Get key sentences (first few and longest sentences)
        key_sentences = sentences[:5]  # First 5 sentences
        if len(sentences) > 5:
            # Add some longer sentences
            longer_sentences = sorted(sentences[5:], key=len, reverse=True)[:5]
            key_sentences.extend(longer_sentences)
        
        # Detect subject area based on keywords
        subject_area = self.detect_subject_area(clean_content, keywords)
        
        # Assess difficulty
        if reading_level >= 70 and word_count < 500:
            difficulty = 'easy'
        elif reading_level >= 50 and word_count < 1000:
            difficulty = 'medium'
        else:
            difficulty = 'hard'
        
        analysis = {
            'word_count': word_count,
            'sentence_count': sentence_count,
            'reading_level': reading_level,
            'keywords': keywords,
            'entities': entities,
            'key_sentences': key_sentences,
            'subject_area': subject_area,
            'difficulty_level': difficulty
        }
        
        return analysis
    
    def detect_subject_area(self, content: str, keywords: List[Dict[str, Any]]) -> str:
        """Detect the subject area based on content and keywords"""
        
        subject_keywords = {
            'mathematics': ['number', 'equation', 'formula', 'calculate', 'theorem', 'proof', 'algebra', 'geometry', 'math'],
            'science': ['experiment', 'hypothesis', 'theory', 'research', 'analysis', 'method', 'data', 'observation', 'science'],
            'history': ['century', 'war', 'empire', 'revolution', 'ancient', 'medieval', 'modern', 'period', 'history'],
            'literature': ['author', 'novel', 'poem', 'character', 'plot', 'theme', 'symbolism', 'metaphor', 'literature'],
            'computer_science': ['algorithm', 'programming', 'software', 'database', 'network', 'code', 'system', 'computer'],
            'biology': ['cell', 'organism', 'evolution', 'genetics', 'ecosystem', 'species', 'protein', 'biology'],
            'chemistry': ['molecule', 'atom', 'reaction', 'compound', 'element', 'bond', 'solution', 'chemistry'],
            'physics': ['energy', 'force', 'motion', 'wave', 'particle', 'quantum', 'gravity', 'velocity', 'physics']
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
    
    def generate_quiz(self, content: str, num_questions: int = 10, 
                     question_types: List[str] = None) -> Dict[str, Any]:
        """Generate a comprehensive quiz from the given content"""
        
        if question_types is None:
            question_types = ['multiple_choice', 'true_false', 'fill_blank']
        
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
                questions['multiple_choice'] = self.generate_mcq(
                    content, analysis, min(questions_per_type, 6)
                )
            elif q_type == 'true_false':
                questions['true_false'] = self.generate_true_false(
                    content, analysis, min(questions_per_type, 4)
                )
            elif q_type == 'fill_blank':
                questions['fill_blank'] = self.generate_fill_blank(
                    content, analysis, min(questions_per_type, 3)
                )
            elif q_type == 'short_answer':
                questions['short_answer'] = self.generate_short_answer(
                    content, analysis, min(questions_per_type, 3)
                )
        
        # Calculate total time
        time_per_question = {
            'multiple_choice': 1.5,
            'true_false': 1,
            'fill_blank': 2,
            'short_answer': 5
        }
        
        total_time = 0
        for q_type, q_list in questions.items():
            total_time += len(q_list) * time_per_question.get(q_type, 2)
        
        estimated_time = max(5, int(total_time))
        
        # Compile final quiz
        quiz_data = {
            'quiz_id': f"simple_quiz_{random.randint(1000, 9999)}",
            'content_analysis': analysis,
            'questions': questions,
            'total_questions': sum(len(q_list) for q_list in questions.values()),
            'estimated_time': estimated_time,
            'difficulty_level': analysis['difficulty_level']
        }
        
        return quiz_data
    
    def generate_mcq(self, content: str, analysis: Dict[str, Any], num_questions: int) -> List[Dict[str, Any]]:
        """Generate multiple choice questions"""
        questions = []
        keywords = analysis['keywords'][:10]
        entities = analysis['entities'][:10]
        key_sentences = analysis['key_sentences'][:num_questions*2]
        
        question_count = 0
        for sentence in key_sentences:
            if question_count >= num_questions:
                break
                
            # Try to create question from sentence
            question_data = self.create_mcq_from_sentence(sentence, keywords, entities)
            if question_data:
                questions.append(question_data)
                question_count += 1
        
        return questions
    
    def create_mcq_from_sentence(self, sentence: str, keywords: List[Dict], entities: List[Dict]) -> Dict[str, Any]:
        """Create a multiple choice question from a sentence"""
        
        # Find important terms in the sentence
        words = self.tokenize_words(sentence)
        important_terms = [word for word in words if len(word) > 3 and word not in self.stop_words]
        
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
        distractors = self.generate_distractors(correct_answer, important_terms, entities, keywords)
        
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
    
    def generate_distractors(self, correct_answer: str, terms: List[str], 
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
        
        # If not enough distractors, create some generic ones
        generic_distractors = ['None of the above', 'All of the above', 'Cannot be determined', 'Not mentioned']
        for distractor in generic_distractors:
            if len(distractors) >= 3:
                break
            if distractor not in distractors:
                distractors.append(distractor)
        
        return distractors[:3]
    
    def generate_true_false(self, content: str, analysis: Dict[str, Any], num_questions: int) -> List[Dict[str, Any]]:
        """Generate true/false questions"""
        questions = []
        key_sentences = analysis['key_sentences'][:num_questions*2]
        
        question_count = 0
        for sentence in key_sentences:
            if question_count >= num_questions:
                break
            
            # Create true statement
            true_question = {
                'question': f"True or False: {sentence.strip('.')}",
                'correct_answer': 'True',
                'explanation': "This statement is directly from the content.",
                'difficulty': 'easy',
                'topic': 'comprehension'
            }
            questions.append(true_question)
            question_count += 1
            
            if question_count >= num_questions:
                break
                
            # Create false statement by modifying the sentence
            modified_sentence = self.modify_sentence_for_false(sentence)
            if modified_sentence and modified_sentence != sentence:
                false_question = {
                    'question': f"True or False: {modified_sentence.strip('.')}",
                    'correct_answer': 'False',
                    'explanation': "This statement contradicts the content.",
                    'difficulty': 'easy',
                    'topic': 'comprehension'
                }
                questions.append(false_question)
                question_count += 1
        
        return questions[:num_questions]
    
    def modify_sentence_for_false(self, sentence: str) -> str:
        """Modify a sentence to make it false"""
        
        # Simple modifications
        modifications = [
            ('is', 'is not'),
            ('are', 'are not'),
            ('was', 'was not'),
            ('were', 'were not'),
            ('can', 'cannot'),
            ('will', 'will not'),
            ('always', 'never'),
            ('never', 'always'),
            ('all', 'none'),
            ('most', 'few'),
            ('many', 'few'),
            ('increase', 'decrease'),
            ('before', 'after'),
            ('above', 'below')
        ]
        
        sentence_lower = sentence.lower()
        for original, replacement in modifications:
            if f' {original} ' in sentence_lower:
                return sentence.replace(f' {original} ', f' {replacement} ', 1)
        
        return None
    
    def generate_fill_blank(self, content: str, analysis: Dict[str, Any], num_questions: int) -> List[Dict[str, Any]]:
        """Generate fill-in-the-blank questions"""
        questions = []
        keywords = analysis['keywords'][:15]
        key_sentences = analysis['key_sentences'][:num_questions*2]
        
        question_count = 0
        for sentence in key_sentences:
            if question_count >= num_questions:
                break
                
            question_data = self.create_fill_blank_question(sentence, keywords)
            if question_data:
                questions.append(question_data)
                question_count += 1
        
        return questions
    
    def create_fill_blank_question(self, sentence: str, keywords: List[Dict]) -> Dict[str, Any]:
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
    
    def generate_short_answer(self, content: str, analysis: Dict[str, Any], num_questions: int) -> List[Dict[str, Any]]:
        """Generate short answer questions"""
        questions = []
        keywords = analysis['keywords'][:10]
        
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

# Initialize quiz generator
quiz_gen = SimpleQuizGenerator()

@app.route('/', methods=['GET'])
def health_check():
    return jsonify({
        'status': 'healthy',
        'service': 'Simple Quiz Generation Service',
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
