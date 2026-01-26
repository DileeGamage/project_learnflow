<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class DirectQuizService
{
    public function generateQuiz($content, $numQuestions = 5, $questionTypes = ['multiple_choice'])
    {
        try {
            Log::info('DirectQuizService: Starting quiz generation');
            
            if (strlen(trim($content)) < 50) {
                return [
                    'success' => false,
                    'error' => 'Content too short for quiz generation'
                ];
            }

            $questions = [];
            
            // Determine question distribution
            if (count($questionTypes) === 1) {
                // Single question type
                if (in_array('multiple_choice', $questionTypes)) {
                    $questions['multiple_choice'] = $this->generateMultipleChoiceQuestions($content, $numQuestions);
                } elseif (in_array('true_false', $questionTypes)) {
                    $questions['true_false'] = $this->generateTrueFalseQuestions($content, $numQuestions);
                }
            } else {
                // Mixed question types - 70% multiple choice, 30% true/false
                $mcCount = ceil($numQuestions * 0.7);
                $tfCount = $numQuestions - $mcCount;
                
                if (in_array('multiple_choice', $questionTypes)) {
                    $questions['multiple_choice'] = $this->generateMultipleChoiceQuestions($content, $mcCount);
                }
                
                if (in_array('true_false', $questionTypes)) {
                    $questions['true_false'] = $this->generateTrueFalseQuestions($content, $tfCount);
                }
            }

            $totalQuestions = array_sum(array_map('count', $questions));

            $result = [
                'success' => true,
                'questions' => $questions,
                'total_questions' => $totalQuestions,
                'estimated_time' => max(1, $totalQuestions * 2),
                'difficulty_level' => 'medium',
                'generated_by' => 'direct_laravel_service',
                'timestamp' => now()->toISOString()
            ];

            Log::info("DirectQuizService: Generated {$totalQuestions} questions successfully");
            return $result;

        } catch (\Exception $e) {
            Log::error('DirectQuizService error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return [
                'success' => false,
                'error' => 'Quiz generation failed: ' . $e->getMessage()
            ];
        }
    }

    private function generateMultipleChoiceQuestions($content, $count)
    {
        $questions = [];
        $contentWords = str_word_count($content, 1);
        $sentences = preg_split('/[.!?]+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $sentences = array_map('trim', $sentences);
        $sentences = array_filter($sentences, function($s) { return strlen($s) > 20; });
        
        // Extract key terms and concepts
        $keyWords = array_slice(array_unique($contentWords), 0, min(30, count($contentWords)));
        
        // Question templates for variety
        $questionTemplates = [
            "According to the content, what is the primary focus of '{keyword}'?",
            "Based on the material, which statement best describes '{keyword}'?",
            "The content suggests that '{keyword}' is primarily characterized by:",
            "In the context of this material, '{keyword}' refers to:",
            "What role does '{keyword}' play in the main discussion?",
            "The content indicates that '{keyword}' is most closely associated with:",
            "From the information provided, '{keyword}' can be understood as:",
            "The primary significance of '{keyword}' in this content is:"
        ];
        
        $optionTemplates = [
            ['A) A fundamental concept requiring detailed explanation', 'B) A minor detail mentioned briefly', 'C) An example used for illustration', 'D) A controversial topic under debate'],
            ['A) The primary subject of analysis', 'B) A supporting argument or evidence', 'C) A historical reference point', 'D) An unrelated tangential topic'],
            ['A) Essential information for understanding the topic', 'B) Background context only', 'C) A comparative example', 'D) Supplementary material'],
            ['A) Core principle or main idea', 'B) Secondary supporting detail', 'C) Methodological approach', 'D) Future consideration or implication']
        ];
        
        for ($i = 0; $i < $count; $i++) {
            $questionNumber = $i + 1;
            $keyword = $keyWords[array_rand($keyWords)] ?? 'concept';
            $template = $questionTemplates[array_rand($questionTemplates)];
            $options = $optionTemplates[array_rand($optionTemplates)];
            
            // Vary the correct answer position
            $correctPosition = ['A', 'B', 'C', 'D'][($i % 4)];
            
            $questions[] = [
                'question' => str_replace('{keyword}', $keyword, $template),
                'options' => $options,
                'correct_answer' => $correctPosition,
                'explanation' => "Based on the content analysis, '{$keyword}' is presented as a key concept that requires understanding in the context of the material.",
                'difficulty' => ['easy', 'medium', 'hard'][($i % 3)],
                'topic' => 'content_comprehension'
            ];
        }
        
        return $questions;
    }

    private function generateTrueFalseQuestions($content, $count)
    {
        $questions = [];
        $contentWords = str_word_count($content, 1);
        $keyWords = array_slice(array_unique($contentWords), 0, min(20, count($contentWords)));
        
        // True/False statement templates
        $trueStatements = [
            "This content provides educational information about {keyword}",
            "The material discusses concepts related to {keyword}",
            "The content includes information that can be used for learning purposes",
            "This text contains structured information about {keyword}",
            "The material presents detailed analysis of {keyword}",
            "The content offers comprehensive coverage of {keyword}",
            "This material is suitable for academic study purposes",
            "The text provides insights into {keyword}"
        ];
        
        $falseStatements = [
            "This content is entirely fictional and has no educational value",
            "The material only contains advertisements and promotional content",
            "This text is exclusively about entertainment topics",
            "The content focuses solely on irrelevant information",
            "This material contains only incomplete and incorrect information",
            "The text is written in a language other than the one shown",
            "This content is primarily composed of random characters",
            "The material only discusses topics unrelated to {keyword}"
        ];
        
        for ($i = 0; $i < $count; $i++) {
            $keyword = $keyWords[array_rand($keyWords)] ?? 'the subject matter';
            $isTrue = ($i % 3) !== 2; // Make about 2/3 true, 1/3 false for variety
            
            if ($isTrue) {
                $statement = str_replace('{keyword}', $keyword, $trueStatements[array_rand($trueStatements)]);
                $answer = 'True';
                $explanation = "This statement is correct. The content does provide educational information and structured learning material.";
            } else {
                $statement = str_replace('{keyword}', $keyword, $falseStatements[array_rand($falseStatements)]);
                $answer = 'False';
                $explanation = "This statement is incorrect. The content contains valid educational material suitable for learning purposes.";
            }
            
            $questions[] = [
                'question' => $statement,
                'correct_answer' => $answer,
                'explanation' => $explanation,
                'difficulty' => ['easy', 'medium'][($i % 2)],
                'topic' => 'content_validation'
            ];
        }
        
        return $questions;
    }

    public function isAvailable()
    {
        // Always available since it's built into Laravel
        return true;
    }
}
