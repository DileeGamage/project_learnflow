<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Exception;

class OpenAIQuizService
{
    protected $model;
    protected $maxTokens;
    protected $temperature;

    public function __construct()
    {
        $this->model = Config::get('services.openai.model', 'gpt-3.5-turbo');
        $this->maxTokens = Config::get('services.openai.max_tokens', 2000);
        $this->temperature = Config::get('services.openai.temperature', 0.7);
    }

    /**
     * Generate quiz questions using OpenAI ChatGPT
     *
     * @param string $content
     * @param array $options
     * @return array
     */
    public function generateQuiz(string $content, array $options = []): array
    {
        try {
            $numQuestions = $options['num_questions'] ?? 10;
            $questionTypes = $options['question_types'] ?? ['multiple_choice', 'true_false', 'fill_blank', 'short_answer'];
            $difficulty = $options['difficulty'] ?? 'medium';
            $subject = $options['subject_area'] ?? 'general';

            // Clean and prepare content
            $cleanContent = $this->cleanContent($content);
            
            if (strlen($cleanContent) < 50) {
                throw new Exception('Content too short for quiz generation');
            }

            // Generate questions using ChatGPT
            $questions = $this->callOpenAI($cleanContent, $numQuestions, $questionTypes, $difficulty, $subject);
            
            // Analyze content
            $contentAnalysis = $this->analyzeContent($cleanContent);

            return [
                'success' => true,
                'quiz' => [
                    'questions' => $questions,
                    'total_questions' => $this->countTotalQuestions($questions),
                    'estimated_time' => $this->calculateEstimatedTime($questions),
                    'difficulty_level' => $difficulty,
                    'content_analysis' => $contentAnalysis,
                    'generated_by' => 'openai_chatgpt',
                    'generation_time' => now()->toISOString(),
                ]
            ];

        } catch (Exception $e) {
            Log::error('OpenAI Quiz Generation Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Call OpenAI API to generate quiz questions
     */
    protected function callOpenAI(string $content, int $numQuestions, array $questionTypes, string $difficulty, string $subject): array
    {
        // Create sophisticated prompt for ChatGPT
        $prompt = $this->buildPrompt($content, $numQuestions, $questionTypes, $difficulty, $subject);

        try {
            $response = OpenAI::chat()->create([
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert educational content creator specializing in quiz generation. You create high-quality, engaging quiz questions that test comprehension, analysis, and critical thinking. Always return valid JSON.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
                'response_format' => ['type' => 'json_object']
            ]);

            $aiResponse = $response->choices[0]->message->content;
            $questions = json_decode($aiResponse, true);

            if (!$questions || !isset($questions['questions'])) {
                throw new Exception('Invalid response format from OpenAI');
            }

            return $this->processQuestions($questions['questions']);

        } catch (Exception $e) {
            Log::error('OpenAI API Error: ' . $e->getMessage());
            throw new Exception('Failed to generate questions: ' . $e->getMessage());
        }
    }

    /**
     * Build sophisticated prompt for ChatGPT
     */
    protected function buildPrompt(string $content, int $numQuestions, array $questionTypes, string $difficulty, string $subject): string
    {
        $typeDescriptions = [
            'multiple_choice' => 'Multiple choice questions with 4 options (A, B, C, D) and clear explanations',
            'true_false' => 'True/False questions that test specific facts and concepts',
            'fill_blank' => 'Fill-in-the-blank questions testing key terms and concepts',
            'short_answer' => 'Short answer questions requiring 1-2 sentence responses'
        ];

        $selectedTypes = array_intersect_key($typeDescriptions, array_flip($questionTypes));
        $typeList = implode(', ', array_keys($selectedTypes));

        $difficultyGuide = [
            'easy' => 'Focus on basic recall and simple comprehension',
            'medium' => 'Include analysis, application, and some synthesis',
            'hard' => 'Emphasize critical thinking, evaluation, and complex analysis'
        ];

        $prompt = "Please generate {$numQuestions} high-quality quiz questions based on the following content. 

**Content to analyze:**
{$content}

**Requirements:**
- Question types to include: {$typeList}
- Difficulty level: {$difficulty} ({$difficultyGuide[$difficulty]})
- Subject area: {$subject}
- Distribute questions evenly across question types
- Each question should test different aspects of the content
- Avoid repetitive or overly similar questions

**For multiple choice questions:**
- Provide 4 realistic options (A, B, C, D)
- Include plausible distractors
- Only one correct answer
- Add explanation for the correct answer

**For true/false questions:**
- Test specific facts or concepts
- Avoid obvious or trivial statements
- Include explanation for the correct answer

**For fill-in-the-blank questions:**
- Focus on key terms, names, dates, or concepts
- Provide clear context clues
- Include the correct answer and brief explanation

**For short answer questions:**
- Ask for analysis, explanation, or application
- Expect 1-2 sentence responses
- Provide sample answer or key points

**Response Format (JSON):**
```json
{
  \"questions\": {
    \"multiple_choice\": [
      {
        \"question\": \"Question text here?\",
        \"options\": [\"A) Option 1\", \"B) Option 2\", \"C) Option 3\", \"D) Option 4\"],
        \"correct_answer\": \"A) Option 1\",
        \"explanation\": \"Explanation of why this is correct\",
        \"difficulty\": \"medium\",
        \"topic\": \"Main topic/concept\"
      }
    ],
    \"true_false\": [
      {
        \"question\": \"Statement to evaluate\",
        \"correct_answer\": \"True\",
        \"explanation\": \"Explanation of why this is true/false\",
        \"difficulty\": \"medium\",
        \"topic\": \"Main topic/concept\"
      }
    ],
    \"fill_blank\": [
      {
        \"question\": \"Text with _____ blank to fill\",
        \"correct_answer\": \"missing word/phrase\",
        \"explanation\": \"Brief explanation\",
        \"difficulty\": \"medium\",
        \"topic\": \"Main topic/concept\"
      }
    ],
    \"short_answer\": [
      {
        \"question\": \"Question requiring short explanation?\",
        \"sample_answer\": \"Expected response or key points\",
        \"explanation\": \"What makes a good answer\",
        \"difficulty\": \"medium\",
        \"topic\": \"Main topic/concept\"
      }
    ]
  }
}
```

Please generate engaging, educational questions that truly test understanding of the content.";

        return $prompt;
    }

    /**
     * Process and validate questions from OpenAI response
     */
    protected function processQuestions(array $questions): array
    {
        $processed = [];

        foreach ($questions as $type => $questionList) {
            if (!is_array($questionList)) continue;
            
            $processed[$type] = [];
            
            foreach ($questionList as $question) {
                if ($this->validateQuestion($question, $type)) {
                    $processed[$type][] = $this->formatQuestion($question, $type);
                }
            }
        }

        return $processed;
    }

    /**
     * Validate question structure
     */
    protected function validateQuestion(array $question, string $type): bool
    {
        if (empty($question['question']) || empty($question['topic'])) {
            return false;
        }

        switch ($type) {
            case 'multiple_choice':
                return isset($question['options'], $question['correct_answer']) && 
                       is_array($question['options']) && count($question['options']) >= 4;
            
            case 'true_false':
                return isset($question['correct_answer']) && 
                       in_array($question['correct_answer'], ['True', 'False', 'true', 'false']);
            
            case 'fill_blank':
                return isset($question['correct_answer']) && 
                       strpos($question['question'], '_') !== false;
            
            case 'short_answer':
                return isset($question['sample_answer']) || isset($question['explanation']);
            
            default:
                return false;
        }
    }

    /**
     * Format question for consistent structure
     */
    protected function formatQuestion(array $question, string $type): array
    {
        $formatted = [
            'question' => trim($question['question']),
            'topic' => $question['topic'] ?? 'General',
            'difficulty' => $question['difficulty'] ?? 'medium',
            'explanation' => $question['explanation'] ?? '',
            'type' => $type
        ];

        switch ($type) {
            case 'multiple_choice':
                $formatted['options'] = $question['options'];
                $formatted['correct_answer'] = $question['correct_answer'];
                break;
            
            case 'true_false':
                $formatted['correct_answer'] = ucfirst(strtolower($question['correct_answer']));
                break;
            
            case 'fill_blank':
                $formatted['correct_answer'] = $question['correct_answer'];
                break;
            
            case 'short_answer':
                $formatted['sample_answer'] = $question['sample_answer'] ?? '';
                break;
        }

        return $formatted;
    }

    /**
     * Analyze content using OpenAI
     */
    protected function analyzeContent(string $content): array
    {
        try {
            $analysisPrompt = "Analyze this educational content and provide a JSON response with the following information:

Content: {$content}

Please provide:
```json
{
  \"word_count\": number,
  \"reading_level\": \"elementary|middle|high|college\",
  \"subject_area\": \"main subject\",
  \"key_topics\": [\"topic1\", \"topic2\", \"topic3\"],
  \"difficulty_assessment\": \"easy|medium|hard\",
  \"content_type\": \"lecture|textbook|article|notes\",
  \"recommended_study_time\": \"X minutes\"
}
```";

            $response = OpenAI::chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $analysisPrompt]
                ],
                'max_tokens' => 500,
                'temperature' => 0.3,
                'response_format' => ['type' => 'json_object']
            ]);

            $analysis = json_decode($response->choices[0]->message->content, true);
            
            return $analysis ?: $this->getDefaultAnalysis($content);

        } catch (Exception $e) {
            Log::warning('Content analysis failed: ' . $e->getMessage());
            return $this->getDefaultAnalysis($content);
        }
    }

    /**
     * Get default content analysis
     */
    protected function getDefaultAnalysis(string $content): array
    {
        return [
            'word_count' => str_word_count($content),
            'reading_level' => 'medium',
            'subject_area' => 'general',
            'key_topics' => ['content analysis', 'learning material'],
            'difficulty_assessment' => 'medium',
            'content_type' => 'notes',
            'recommended_study_time' => ceil(str_word_count($content) / 200) . ' minutes'
        ];
    }

    /**
     * Clean content for processing
     */
    protected function cleanContent(string $content): string
    {
        // Remove HTML tags
        $content = strip_tags($content);
        
        // Remove excessive whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        
        // Trim
        $content = trim($content);
        
        // Limit length to prevent API token limits
        if (strlen($content) > 8000) {
            $content = substr($content, 0, 8000) . '...';
        }
        
        return $content;
    }

    /**
     * Count total questions across all types
     */
    protected function countTotalQuestions(array $questions): int
    {
        $total = 0;
        foreach ($questions as $type => $questionList) {
            $total += is_array($questionList) ? count($questionList) : 0;
        }
        return $total;
    }

    /**
     * Calculate estimated time for quiz
     */
    protected function calculateEstimatedTime(array $questions): int
    {
        $timePerType = [
            'multiple_choice' => 1.5,
            'true_false' => 0.5,
            'fill_blank' => 1.0,
            'short_answer' => 2.5
        ];

        $totalTime = 0;
        foreach ($questions as $type => $questionList) {
            if (is_array($questionList)) {
                $timePerQuestion = $timePerType[$type] ?? 1.5;
                $totalTime += count($questionList) * $timePerQuestion;
            }
        }

        return max(5, ceil($totalTime));
    }

    /**
     * Check if OpenAI service is available
     */
    public function isAvailable(): bool
    {
        return !empty(Config::get('services.openai.api_key'));
    }
}
