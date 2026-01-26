<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GeminiQuizService
{
    protected $serviceUrl;
    protected $apiKey;
    protected $timeout;

    public function __construct()
    {
        $this->serviceUrl = config('services.gemini_quiz.url', 'http://localhost:5003');
        $this->apiKey = config('services.gemini_quiz.api_key');
        $this->timeout = config('services.gemini_quiz.timeout', 90);
    }

    /**
     * Generate a quiz using Gemini AI
     *
     * @param string $content The content to generate quiz from
     * @param array $options Quiz generation options
     * @return array
     */
    public function generateQuiz(string $content, array $options = []): array
    {
        try {
            // Clean and prepare content
            $cleanContent = $this->cleanContent($content);
            
            if (empty($cleanContent)) {
                return [
                    'success' => false,
                    'error' => 'Content is empty or too short for quiz generation'
                ];
            }

            // Prepare request data
            $requestData = [
                'content' => $cleanContent,
                'num_questions' => $options['num_questions'] ?? 10,
                'question_types' => $options['question_types'] ?? ['multiple_choice', 'true_false'],
                'difficulty' => $options['difficulty'] ?? 'medium'
            ];

            Log::info('Calling Gemini quiz service', [
                'url' => $this->serviceUrl,
                'content_length' => strlen($cleanContent),
                'num_questions' => $requestData['num_questions']
            ]);

            // Call the Gemini service
            $result = $this->callGeminiService($requestData);

            if ($result['success']) {
                Log::info('Gemini quiz generated successfully');
                return $result;
            } else {
                Log::error('Gemini quiz generation failed', ['error' => $result['error'] ?? 'Unknown error']);
                return $result;
            }

        } catch (\Exception $e) {
            Log::error('Gemini quiz generation exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to generate quiz: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Call the Gemini quiz generation service
     *
     * @param array $data
     * @return array
     */
    protected function callGeminiService(array $data): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->serviceUrl . '/generate-quiz', $data);

            if ($response->successful()) {
                return $response->json();
            } else {
                $errorBody = $response->body();
                $status = $response->status();
                
                Log::error('Gemini service returned error', [
                    'status' => $status,
                    'body' => $errorBody
                ]);

                // Handle rate limit errors specially
                if ($status == 500 && str_contains($errorBody, '429')) {
                    // Extract retry time from error message if possible
                    if (preg_match('/retry in (\d+(?:\.\d+)?)s/', $errorBody, $matches)) {
                        $retrySeconds = ceil((float)$matches[1]);
                        return [
                            'success' => false,
                            'error' => "Gemini API rate limit exceeded. Free tier allows 1500 requests/day. Please wait {$retrySeconds} seconds and try again, or upgrade your API key for higher limits."
                        ];
                    }
                    
                    return [
                        'success' => false,
                        'error' => 'Gemini API rate limit exceeded. Free tier quota reached. Please wait a few minutes and try again, or upgrade your API key for higher limits.'
                    ];
                }

                return [
                    'success' => false,
                    'error' => 'Gemini AI Service returned HTTP ' . $status
                ];
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Cannot connect to Gemini service', [
                'url' => $this->serviceUrl,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Cannot connect to Gemini AI service. Please ensure the service is running on port 5003.'
            ];

        } catch (\Exception $e) {
            Log::error('Gemini service call failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Gemini AI service error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check if the Gemini service is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        try {
            if (empty($this->apiKey)) {
                Log::warning('Gemini API key not configured');
                return false;
            }

            $response = Http::timeout(5)->get($this->serviceUrl . '/health');
            
            if ($response->successful()) {
                $health = $response->json();
                return ($health['status'] ?? '') === 'healthy';
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Gemini health check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get service information
     *
     * @return array
     */
    public function getServiceInfo(): array
    {
        return [
            'name' => 'Gemini AI Quiz Service',
            'url' => $this->serviceUrl,
            'model' => 'gemini-2.5-flash',
            'cost' => 'Free tier',
            'features' => [
                'Context-aware question generation',
                'High-quality educational questions',
                'Multiple choice and true/false support',
                'Automatic difficulty adjustment'
            ]
        ];
    }

    /**
     * Clean and prepare content for quiz generation
     *
     * @param string $content
     * @return string
     */
    protected function cleanContent(string $content): string
    {
        // Remove excessive whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        
        // Remove HTML tags
        $content = strip_tags($content);
        
        // Trim
        $content = trim($content);
        
        // Limit length (Gemini can handle large context, but let's be reasonable)
        $maxLength = 30000;
        if (strlen($content) > $maxLength) {
            $content = substr($content, 0, $maxLength) . '...';
            Log::info('Content truncated for Gemini', [
                'original_length' => strlen($content),
                'truncated_to' => $maxLength
            ]);
        }
        
        return $content;
    }
}
