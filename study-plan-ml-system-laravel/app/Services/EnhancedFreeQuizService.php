<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Exception;

class EnhancedFreeQuizService
{
    protected $serviceUrl;
    protected $timeout;

    public function __construct()
    {
        $this->serviceUrl = Config::get('services.enhanced_free_quiz.url', 'http://localhost:5002');
        $this->timeout = Config::get('services.enhanced_free_quiz.timeout', 180);
    }

    /**
     * Intelligently chunk content to cover the entire document
     */
    protected function sectionBasedChunking(string $content, int $maxChunkSize = 3000): array
    {
        $totalLength = strlen($content);
        
        if ($totalLength <= $maxChunkSize) {
            return [$content];
        }
        
        Log::info("Content is {$totalLength} chars, using section-based chunking...");
        
        // Split by pages or major sections
        $sections = preg_split('/--- Page \d+ ---/', $content);
        $sections = array_filter($sections, fn($s) => strlen(trim($s)) > 100);
        
        if (empty($sections)) {
            // Fallback: split by paragraphs
            $sections = preg_split('/\n\n+/', $content);
        }
        
        // Group sections to fit within chunk size
        $chunks = [];
        $currentChunk = '';
        
        foreach ($sections as $section) {
            $section = trim($section);
            
            // If section alone is too large, split it
            if (strlen($section) > $maxChunkSize) {
                // Save current chunk if exists
                if (!empty($currentChunk)) {
                    $chunks[] = $currentChunk;
                    $currentChunk = '';
                }
                // Split large section into smaller parts
                $parts = str_split($section, $maxChunkSize);
                foreach ($parts as $part) {
                    $chunks[] = $part;
                }
                continue;
            }
            
            // If adding this section exceeds chunk size, save current chunk
            if (strlen($currentChunk) + strlen($section) > $maxChunkSize && !empty($currentChunk)) {
                $chunks[] = $currentChunk;
                $currentChunk = '';
            }
            
            $currentChunk .= $section . "\n\n";
        }
        
        // Add last chunk
        if (!empty($currentChunk)) {
            $chunks[] = $currentChunk;
        }
        
        Log::info("Created " . count($chunks) . " section-based chunks");
        
        return $chunks;
    }

    /**
     * Generate quiz questions using Phi-3-Mini AI with intelligent chunking
     *
     * @param string $content
     * @param array $options
     * @return array
     */
    public function generateQuiz(string $content, array $options = []): array
    {
        // Increase PHP execution time for multi-chunk processing
        set_time_limit(600); // 10 minutes for multiple chunks
        
        try {
            $numQuestions = $options['num_questions'] ?? 10;
            $questionTypes = $options['question_types'] ?? ['multiple_choice', 'true_false'];

            // Check if service is available
            if (!$this->isAvailable()) {
                throw new Exception('Phi-3-Mini AI service is not available');
            }

            // Instead of splitting into chunks, take a SMART SAMPLE of the content
            $cleanContent = strip_tags($content);
            
            // Strategy: Take first 10KB (most important intro concepts) + sample from middle
            $contentLength = strlen($cleanContent);
            $sampleContent = '';
            
            if ($contentLength <= 10000) {
                // Small document: use all
                $sampleContent = $cleanContent;
                Log::info("Content is {$contentLength} chars, using all content");
            } else {
                // Large document: smart sampling
                // Take first 6KB (intro + main concepts)
                $sampleContent = substr($cleanContent, 0, 6000);
                
                // Add middle section (2KB)
                $middleStart = (int)($contentLength / 2) - 1000;
                $sampleContent .= "\n\n" . substr($cleanContent, $middleStart, 2000);
                
                // Add ending section (2KB for conclusions)
                $sampleContent .= "\n\n" . substr($cleanContent, -2000);
                
                Log::info("Content is {$contentLength} chars, sampled 10KB (intro + middle + end)");
            }
            
            $allQuestions = [
                'multiple_choice' => [],
                'true_false' => []
            ];
            
            // Generate ALL questions in ONE API call (not per chunk)
            Log::info("Generating {$numQuestions} questions from sampled content...");
            
            $response = Http::timeout(180) // 3 minutes for full quiz generation
                ->post($this->serviceUrl . '/generate-quiz', [
                    'content' => $sampleContent,
                    'num_questions' => $numQuestions
                ]);
            
            if ($response->successful()) {
                $result = $response->json();
                $quizData = $result['quiz_data'] ?? $result['quiz'] ?? null;
                
                if (isset($result['success']) && $result['success'] && $quizData) {
                    $allQuestions['multiple_choice'] = $quizData['multiple_choice'] ?? [];
                    $allQuestions['true_false'] = $quizData['true_false'] ?? [];
                    
                    Log::info("Generated: " . 
                        count($allQuestions['multiple_choice']) . " MCQ, " .
                        count($allQuestions['true_false']) . " T/F");
                } else {
                    throw new Exception('Invalid response from Phi-3 service');
                }
            } else {
                throw new Exception('Failed to generate quiz: ' . $response->body());
            }
            
            // REMOVE OLD CHUNKING LOOP - replaced with single API call above
            if (false) {
            foreach ($chunks_DISABLED as $index => $chunk) {
                Log::info("Generating questions from chunk (DISABLED)");
                
                // Call Phi-3 service for this chunk (2 minute timeout per chunk)
                $response = Http::timeout(120)
                    ->post($this->serviceUrl . '/generate-quiz', [
                        'content' => $chunk,
                        'num_questions' => $questionsPerChunk
                    ]);
                
                if ($response->successful()) {
                    $result = $response->json();
                    
                    // Handle both 'quiz' and 'quiz_data' keys for compatibility
                    $quizData = $result['quiz_data'] ?? $result['quiz'] ?? null;
                    
                    if (isset($result['success']) && $result['success'] && $quizData) {
                        // Merge questions from this chunk
                        if (isset($quizData['multiple_choice'])) {
                            $allQuestions['multiple_choice'] = array_merge(
                                $allQuestions['multiple_choice'],
                                $quizData['multiple_choice']
                            );
                        }
                        if (isset($quizData['true_false'])) {
                            $allQuestions['true_false'] = array_merge(
                                $allQuestions['true_false'],
                                $quizData['true_false']
                            );
                        }
                        
                        Log::info("Chunk {$index} generated: " . 
                            count($quizData['multiple_choice'] ?? []) . " MCQ, " .
                            count($quizData['true_false'] ?? []) . " T/F");
                    }
                }
                
                // Stop if we have enough questions
                $totalGenerated = count($allQuestions['multiple_choice']) + count($allQuestions['true_false']);
                if ($totalGenerated >= $numQuestions) {
                    break;
                }
            }
            } // END OF DISABLED LOOP
            
            $totalGenerated = count($allQuestions['multiple_choice']) + count($allQuestions['true_false']);
            
            Log::info("Phi-3 quiz generation complete", [
                'total_questions' => $totalGenerated,
                'api_calls' => 1, // Single API call instead of chunked
                'mcq' => count($allQuestions['multiple_choice']),
                'tf' => count($allQuestions['true_false'])
            ]);

            // Format questions for database storage - keep MCQ and T/F separate as view expects
            Log::info("Formatting questions", [
                'mcq_to_format' => count($allQuestions['multiple_choice']),
                'tf_to_format' => count($allQuestions['true_false'])
            ]);
            
            // Format MCQ questions
            $formattedMCQ = [];
            foreach ($allQuestions['multiple_choice'] as $mcq) {
                $formattedMCQ[] = [
                    'question' => $mcq['question'],
                    'options' => $mcq['options'],
                    'correct_answer' => $mcq['correct_answer'],
                    'explanation' => $mcq['explanation'] ?? ''
                ];
            }
            
            // Format T/F questions  
            $formattedTF = [];
            foreach ($allQuestions['true_false'] as $tf) {
                $formattedTF[] = [
                    'question' => $tf['question'],
                    'correct_answer' => $tf['answer'] ? 'True' : 'False',
                    'explanation' => $tf['explanation'] ?? ''
                ];
            }
            
            // Combine in the format the view expects
            $formattedQuestions = [
                'multiple_choice' => $formattedMCQ,
                'true_false' => $formattedTF
            ];

            Log::info("Questions formatted", [
                'mcq_count' => count($formattedMCQ),
                'tf_count' => count($formattedTF),
                'total' => count($formattedMCQ) + count($formattedTF)
            ]);

            return [
                'success' => true,
                'quiz' => [
                    'questions' => $formattedQuestions,
                    'total_questions' => $totalGenerated,
                    'estimated_time' => (int)($totalGenerated * 1.5), // 1.5 min per question
                    'difficulty_level' => $options['difficulty'] ?? 'medium',
                    'generated_by' => 'hybrid_quiz_generator',
                    'generation_time' => now()->toISOString(),
                    'models_used' => 'Smart Templates + Concept Extraction',
                    'cost' => '$0.00 (Free)',
                    'content_analysis' => [
                        'api_calls' => 1,
                        'sampling_strategy' => 'smart_sampling_10KB',
                        'coverage' => 'intro_middle_end',
                        'mcq_count' => count($allQuestions['multiple_choice']),
                        'tf_count' => count($allQuestions['true_false'])
                    ]
                ]
            ];

        } catch (Exception $e) {
            Log::error('Phi-3 Quiz generation error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Call Phi-3-Mini AI Service
     */
    protected function callEnhancedFreeService(string $content, int $numQuestions, array $questionTypes): ?array
    {
        try {
            $data = [
                'content' => $content,
                'num_questions' => $numQuestions,
                'question_types' => $questionTypes
            ];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->serviceUrl . '/generate-quiz',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ],
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_CONNECTTIMEOUT => 10
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_errno($ch)) {
                throw new Exception('Phi-3-Mini AI Service connection error: ' . curl_error($ch));
            }
            
            curl_close($ch);

            if ($httpCode !== 200) {
                throw new Exception("Phi-3-Mini AI Service returned HTTP $httpCode");
            }

            $result = json_decode($response, true);
            
            if (!$result || !isset($result['success']) || !$result['success']) {
                $error = $result['error'] ?? 'Unknown error from Phi-3-Mini AI Service';
                throw new Exception($error);
            }

            return $result['quiz_data'] ?? $result['quiz'] ?? null;

        } catch (Exception $e) {
            Log::error('Phi-3-Mini AI Service call failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if Phi-3-Mini AI service is available
     */
    public function isAvailable(): bool
    {
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->serviceUrl . '/health',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            Log::info('Phi-3-Mini Quiz Service Check', [
                'url' => $this->serviceUrl . '/health',
                'http_code' => $httpCode,
                'response' => $response,
                'curl_error' => $error,
                'available' => $httpCode === 200
            ]);

            return $httpCode === 200;

        } catch (Exception $e) {
            Log::error('Phi-3-Mini Quiz Service Availability Check Failed', [
                'error' => $e->getMessage(),
                'url' => $this->serviceUrl
            ]);
            return false;
        }
    }

    /**
     * Get service status and information
     */
    public function getServiceInfo(): array
    {
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->serviceUrl . '/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $info = json_decode($response, true);
                return [
                    'available' => true,
                    'service' => $info['service'] ?? 'Phi-3-Mini Quiz Service',
                    'models' => $info['model'] ?? 'Microsoft Phi-3-Mini-4K-Instruct',
                    'quality' => $info['quality'] ?? 'ChatGPT-level (⭐⭐⭐⭐⭐)',
                    'cost' => $info['cost'] ?? '$0 (Free)',
                    'url' => $this->serviceUrl
                ];
            }

            return [
                'available' => false,
                'error' => "Service not responding (HTTP $httpCode)",
                'url' => $this->serviceUrl
            ];

        } catch (Exception $e) {
            return [
                'available' => false,
                'error' => $e->getMessage(),
                'url' => $this->serviceUrl
            ];
        }
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
        
        // Limit length for processing efficiency
        if (strlen($content) > 10000) {
            $content = substr($content, 0, 10000) . '...';
        }
        
        return $content;
    }
}
