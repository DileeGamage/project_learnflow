"""
Gemini AI Quiz Generation Service using Google's Generative AI

This service provides high-quality quiz generation using Google's Gemini model.
Gemini offers excellent contextual understanding and is available with a free tier.

Features:
- Educational quiz generation with proper question quality
- Context-aware questions about actual concepts
- Free tier: 1500 requests/day (gemini-1.5-flash)
- Large context window for long documents
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import json
import logging
import os
from typing import Dict, List, Any
import google.generativeai as genai

app = Flask(__name__)
CORS(app)

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Configure Gemini API
GEMINI_API_KEY = os.getenv('GEMINI_API_KEY', '')
if GEMINI_API_KEY:
    genai.configure(api_key=GEMINI_API_KEY)
    logger.info("✅ Gemini API configured successfully")
else:
    logger.warning("⚠️ GEMINI_API_KEY not found in environment")


class GeminiQuizGenerator:
    def __init__(self):
        """Initialize the Gemini quiz generation system"""
        logger.info("Initializing Gemini Quiz Generator...")
        
        # Try multiple models in order of preference (2025 latest models first)
        models_to_try = [
            'gemini-2.5-flash',  # Latest 2025 model - fastest and most efficient
            'gemini-2.0-flash',  # December 2024 model - very fast
            'gemini-1.5-flash',  # Reliable fallback
            'gemini-1.5-pro',    # Better quality if available
            'gemini-pro',        # Older but widely available
        ]
        
        last_error = None
        for model_name in models_to_try:
            try:
                self.model_name = model_name
                self.model = genai.GenerativeModel(
                    model_name=self.model_name,
                    generation_config={
                        'temperature': 0.7,
                        'top_p': 0.95,
                        'top_k': 40,
                        'max_output_tokens': 8192,
                    }
                )
                logger.info(f"✅ Gemini model '{self.model_name}' initialized successfully!")
                return  # Success, exit initialization
            except Exception as e:
                logger.warning(f"⚠️ Could not initialize {model_name}: {str(e)}")
                last_error = e
                continue
        
        # If we get here, none of the models worked
        logger.error(f"❌ Failed to initialize any Gemini model. Last error: {str(last_error)}")
        raise Exception(f"Could not initialize any Gemini model. Last error: {str(last_error)}")

    def generate_quiz(self, content: str, num_questions: int = 10, 
                     question_types: List[str] = None) -> Dict[str, Any]:
        """
        Generate a quiz from the given content using Gemini AI
        
        Args:
            content: The educational content to generate questions from
            num_questions: Number of questions to generate
            question_types: List of question types (e.g., ['multiple_choice', 'true_false'])
        
        Returns:
            Dictionary with quiz data
        """
        try:
            if question_types is None:
                question_types = ['multiple_choice', 'true_false']
            
            logger.info(f"Generating quiz with {num_questions} questions using Gemini AI...")
            
            # Create the prompt
            prompt = self._create_quiz_prompt(content, num_questions, question_types)
            
            # Generate response
            response = self.model.generate_content(prompt)
            
            # Parse the response
            quiz_data = self._parse_gemini_response(response.text, num_questions, question_types)
            
            return {
                'success': True,
                'quiz_data': quiz_data  # Changed from 'quiz' to 'quiz_data' for Laravel compatibility
            }
            
        except Exception as e:
            error_msg = str(e)
            logger.error(f"❌ Gemini quiz generation error: {error_msg}")
            
            # Check if it's a rate limit error
            if '429' in error_msg or 'quota' in error_msg.lower() or 'rate limit' in error_msg.lower():
                return {
                    'success': False,
                    'error': f'Gemini API rate limit reached. If you have a paid account, please ensure billing is enabled at https://console.cloud.google.com/billing. Error: {error_msg}'
                }
            
            return {
                'success': False,
                'error': error_msg
            }

    def _create_quiz_prompt(self, content: str, num_questions: int, 
                           question_types: List[str]) -> str:
        """Create a comprehensive prompt for Gemini"""
        
        # Limit content length (Gemini has large context but let's be reasonable)
        max_content_length = 30000
        if len(content) > max_content_length:
            content = content[:max_content_length] + "..."
        
        prompt = f"""You are an expert educational assessment creator. Generate a high-quality quiz based on the following content.

**CONTENT TO ANALYZE:**
{content}

**QUIZ REQUIREMENTS:**
- Generate exactly {num_questions} questions
- Question types: {', '.join(question_types)}
- Questions MUST test understanding of actual concepts from the content
- Avoid trivial questions about common words or formatting
- Focus on key concepts, definitions, relationships, and applications
- Each question should have educational value

**OUTPUT FORMAT (JSON):**
Return ONLY valid JSON in this exact format:
{{
    "questions": {{
        "true_false": [
            {{
                "question": "Clear, specific question about a concept",
                "correct_answer": "True" or "False",
                "explanation": "Why this answer is correct",
                "topic": "Specific topic from content",
                "difficulty": "easy/medium/hard"
            }}
        ],
        "multiple_choice": [
            {{
                "question": "Clear, specific question about a concept",
                "options": ["A) First option", "B) Second option", "C) Third option", "D) Fourth option"],
                "correct_answer": "A",
                "explanation": "Why this answer is correct",
                "topic": "Specific topic from content",
                "difficulty": "easy/medium/hard"
            }}
        ]
    }},
    "estimated_time": 15
}}

**QUALITY GUIDELINES:**
1. Questions about concepts, not about words like "and", "the", "---"
2. Test understanding, not memorization
3. Provide clear explanations
4. Ensure correct answers are accurate
5. Make distractors plausible but incorrect

Generate the quiz now in valid JSON format:"""
        
        return prompt

    def _parse_gemini_response(self, response_text: str, num_questions: int,
                               question_types: List[str]) -> Dict[str, Any]:
        """Parse Gemini's response into structured quiz data"""
        try:
            # Clean the response - remove markdown code blocks if present
            cleaned_text = response_text.strip()
            
            # Remove markdown code blocks
            if cleaned_text.startswith('```'):
                # Find the end of the opening marker
                first_newline = cleaned_text.find('\n')
                if first_newline > 0:
                    cleaned_text = cleaned_text[first_newline + 1:]
                else:
                    cleaned_text = cleaned_text[3:]
            
            if cleaned_text.endswith('```'):
                cleaned_text = cleaned_text[:-3]
            
            cleaned_text = cleaned_text.strip()
            
            # Try to find JSON object if there's extra text
            if not cleaned_text.startswith('{'):
                start_idx = cleaned_text.find('{')
                if start_idx > 0:
                    cleaned_text = cleaned_text[start_idx:]
            
            if not cleaned_text.endswith('}'):
                end_idx = cleaned_text.rfind('}')
                if end_idx > 0:
                    cleaned_text = cleaned_text[:end_idx + 1]
            
            # Parse JSON
            quiz_data = json.loads(cleaned_text)
            
            # Validate and transform structure
            # Gemini returns: {"questions": {"multiple_choice": [...], "true_false": [...]}}
            # Laravel expects: {"multiple_choice": [...], "true_false": [...]}
            if 'questions' in quiz_data:
                # Extract the questions object
                questions = quiz_data['questions']
                result = {
                    'multiple_choice': questions.get('multiple_choice', []),
                    'true_false': questions.get('true_false', []),
                    'estimated_time': quiz_data.get('estimated_time', 15)
                }
            else:
                # Already in correct format
                result = {
                    'multiple_choice': quiz_data.get('multiple_choice', []),
                    'true_false': quiz_data.get('true_false', []),
                    'estimated_time': quiz_data.get('estimated_time', 15)
                }
            
            logger.info("Successfully parsed Gemini response")
            return result
            
        except json.JSONDecodeError as e:
            logger.error(f"Failed to parse Gemini response as JSON: {str(e)}")
            logger.error(f"Response preview: {response_text[:1000]}...")
            
            # Return a fallback structure
            return {
                'true_false': [],
                'multiple_choice': [],
                'estimated_time': 15
            }


# Initialize the quiz generator
quiz_gen = None
if GEMINI_API_KEY:
    try:
        quiz_gen = GeminiQuizGenerator()
    except Exception as e:
        logger.error(f"Failed to initialize quiz generator: {str(e)}")


@app.route('/')
def home():
    """Home endpoint"""
    return jsonify({
        'service': 'Gemini AI Quiz Generation Service',
        'status': 'running',
        'model': 'gemini-2.5-flash',
        'endpoints': {
            '/health': 'Health check',
            '/generate-quiz': 'POST - Generate quiz from content'
        }
    })


@app.route('/health', methods=['GET'])
def health():
    """Health check endpoint"""
    try:
        is_healthy = quiz_gen is not None and bool(GEMINI_API_KEY)
        
        response_data = {
            'status': 'healthy' if is_healthy else 'unhealthy',
            'service': 'Gemini AI Quiz Service',
            'model': 'gemini-2.5-flash',
            'api_configured': bool(GEMINI_API_KEY),
            'model_initialized': quiz_gen is not None
        }
        
        status_code = 200 if is_healthy else 503
        logger.info(f"Health check: {response_data['status']}")
        
        return jsonify(response_data), status_code
        
    except Exception as e:
        logger.error(f"Health check error: {str(e)}")
        return jsonify({
            'status': 'unhealthy',
            'error': str(e)
        }), 503


@app.route('/generate-quiz', methods=['POST'])
def generate_quiz():
    """Generate quiz from content"""
    try:
        if not quiz_gen:
            return jsonify({
                'success': False,
                'error': 'Gemini API key not configured. Please set GEMINI_API_KEY environment variable.'
            }), 503
        
        # Get request data
        data = request.get_json()
        content = data.get('content', '')
        num_questions = data.get('num_questions', 10)
        question_types = data.get('question_types', ['multiple_choice', 'true_false'])
        
        if not content:
            return jsonify({
                'success': False,
                'error': 'No content provided'
            }), 400
        
        # Generate quiz using Gemini AI
        result = quiz_gen.generate_quiz(
            content=content,
            num_questions=num_questions,
            question_types=question_types
        )
        
        if result['success']:
            # Log the structure for debugging
            quiz_data = result.get('quiz_data', {})
            logger.info(f"Quiz generated: {len(quiz_data.get('multiple_choice', []))} MCQ, {len(quiz_data.get('true_false', []))} T/F")
            return jsonify(result)
        else:
            return jsonify(result), 500
        
    except Exception as e:
        logger.error(f"Gemini quiz generation error: {str(e)}")
        return jsonify({
            'success': False,
            'error': 'Failed to generate quiz. Please try again.'
        }), 500


if __name__ == '__main__':
    print("🚀 Starting Gemini AI Quiz Generation Service...")
    print(f"🤖 Model: {GeminiQuizGenerator().model_name}")
    print("💰 Cost: Free tier (1500 requests/day)")
    print("📍 Available at: http://localhost:5003")
    print()
    if not GEMINI_API_KEY:
        print("⚠️  WARNING: GEMINI_API_KEY not found!")
        print("   Get your free API key at: https://makersuite.google.com/app/apikey")
        print("   Then set it: set GEMINI_API_KEY=your_key_here")
        print()
    app.run(debug=True, host='0.0.0.0', port=5003)
