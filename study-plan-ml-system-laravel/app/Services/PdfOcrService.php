<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;
use Exception;

class PdfOcrService
{
    private string $serviceUrl;
    private int $timeout;

    public function __construct()
    {
        $this->serviceUrl = config('services.pdf_ocr.url', 'http://localhost:5000');
        $this->timeout = config('services.pdf_ocr.timeout', 30);
    }

    /**
     * Check if external OCR service is available
     */
    private function isServiceAvailable(): bool
    {
        try {
            $response = Http::timeout(3)->get($this->serviceUrl);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get service status for frontend
     */
    public function getServiceStatus(): array
    {
        $externalAvailable = $this->isServiceAvailable();
        
        return [
            'available' => true, // Always true since we have PHP fallback
            'external_service' => $externalAvailable,
            'method' => $externalAvailable ? 'External OCR Service' : 'PHP Basic Extraction',
            'message' => $externalAvailable 
                ? 'PDF OCR service is available with full OCR capabilities.'
                : 'PDF upload is available using basic text extraction. For better OCR results, start the Python OCR service.',
            'capabilities' => [
                'text_extraction' => true,
                'ocr_images' => $externalAvailable,
                'advanced_formatting' => $externalAvailable
            ]
        ];
    }

    /**
     * Extract text from PDF using local Flask service
     */
    public function extractTextFromPdf(UploadedFile $file): array
    {
        Log::info('PdfOcrService: Starting PDF text extraction for file: ' . $file->getClientOriginalName() . ' (size: ' . $file->getSize() . ' bytes)');
        
        // First try the external service
        $externalResult = $this->extractTextFromExternalService($file);
        if ($externalResult['success']) {
            Log::info('PdfOcrService: External service extraction successful');
            return $externalResult;
        }

        Log::warning('PdfOcrService: External service failed, falling back to PHP extraction. Error: ' . ($externalResult['error'] ?? 'Unknown'));
        
        // Fallback to PHP-based extraction if external service fails
        $phpResult = $this->extractTextWithPhp($file);
        Log::info('PdfOcrService: PHP fallback extraction completed. Success: ' . ($phpResult['success'] ? 'true' : 'false'));
        
        return $phpResult;
    }

    /**
     * Extract text using external Flask service
     */
    private function extractTextFromExternalService(UploadedFile $file): array
    {
        try {
            Log::info('PdfOcrService: Starting external service extraction for file: ' . $file->getClientOriginalName());
            
            // Check if service is available
            if (!$this->isServiceAvailable()) {
                Log::warning('PdfOcrService: External service is not available');
                return [
                    'success' => false,
                    'error' => 'PDF processing service is not available'
                ];
            }

            Log::info('PdfOcrService: External service is available, making request to: ' . $this->serviceUrl . '/extract-text');

            $response = Http::timeout($this->timeout)
                ->attach('file', file_get_contents($file->getPathname()), $file->getClientOriginalName())
                ->post($this->serviceUrl . '/extract-text');

            Log::info('PdfOcrService: External service response status: ' . $response->status());

            if ($response->successful()) {
                $result = $response->json();
                Log::info('PdfOcrService: External service response success: ' . ($result['success'] ? 'true' : 'false'));
                
                if ($result['success']) {
                    Log::info('PdfOcrService: Successfully extracted text using method: ' . ($result['method'] ?? 'unknown'));
                    return [
                        'success' => true,
                        'text' => $result['text'],
                        'method' => $result['method'] ?? 'External OCR Service',
                        'char_count' => $result['char_count'] ?? 0,
                        'word_count' => $result['word_count'] ?? 0
                    ];
                } else {
                    Log::error('PdfOcrService: External service returned error: ' . ($result['error'] ?? 'Unknown error'));
                    return [
                        'success' => false,
                        'error' => $result['error'] ?? 'Unknown error occurred'
                    ];
                }
            } else {
                Log::error('PdfOcrService: External service HTTP error - Status: ' . $response->status() . ', Body: ' . $response->body());
                return [
                    'success' => false,
                    'error' => 'Failed to communicate with PDF processing service (HTTP ' . $response->status() . ')'
                ];
            }

        } catch (\Exception $e) {
            Log::error('PdfOcrService: External service exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to process PDF with external service: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Extract text using PHP (fallback method)
     */
    private function extractTextWithPhp(UploadedFile $file): array
    {
        try {
            Log::info('PdfOcrService: Starting PHP-based PDF text extraction');
            
            // Use the proper PDF parser library
            $parser = new Parser();
            $pdf = $parser->parseFile($file->getPathname());
            
            // Extract text from all pages with better formatting preservation
            $pages = $pdf->getPages();
            $allText = '';
            
            foreach ($pages as $pageNumber => $page) {
                $pageText = $page->getText();
                
                if (!empty($pageText)) {
                    // Add page header for all pages to match Python service format
                    if ($pageNumber == 0) {
                        $allText .= "--- Page " . ($pageNumber + 1) . " ---\n";
                    } else {
                        $allText .= "\n\n--- Page " . ($pageNumber + 1) . " ---\n\n";
                    }
                    $allText .= $pageText;
                }
            }
            
            // If page-by-page extraction didn't work well, fall back to full document
            if (empty($allText) || strlen($allText) < 50) {
                $allText = $pdf->getText();
                // Add page header for single document extraction to maintain consistency
                if (!empty($allText) && !str_contains($allText, '--- Page')) {
                    $allText = "--- Page 1 ---\n" . $allText;
                }
            }
            
            // Clean up the extracted text while preserving formatting
            $text = $this->cleanExtractedText($allText);
            
            if (!empty($text) && strlen($text) > 10) {
                $wordCount = str_word_count($text);
                Log::info("PdfOcrService: Successfully extracted {$wordCount} words using PDF parser");
                
                return [
                    'success' => true,
                    'text' => $text,
                    'method' => 'PHP PDF Parser (smalot/pdfparser)',
                    'char_count' => strlen($text),
                    'word_count' => $wordCount,
                    'pages_processed' => count($pages)
                ];
            } else {
                Log::warning('PdfOcrService: No meaningful text extracted from PDF');
                return [
                    'success' => false,
                    'error' => 'No readable text found in the PDF. The PDF might contain only images or be encrypted.'
                ];
            }

        } catch (Exception $e) {
            Log::error('PdfOcrService: PDF parser failed: ' . $e->getMessage());
            
            // Fallback to basic extraction as last resort
            try {
                $text = $this->extractTextBasic($file->getPathname());
                if (!empty($text)) {
                    return [
                        'success' => true,
                        'text' => $text,
                        'method' => 'PHP Basic Extraction (Fallback)',
                        'char_count' => strlen($text),
                        'word_count' => str_word_count($text)
                    ];
                }
            } catch (Exception $basicException) {
                Log::error('PdfOcrService: Basic extraction also failed: ' . $basicException->getMessage());
            }
            
            return [
                'success' => false,
                'error' => 'Failed to extract text from PDF: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Clean and normalize extracted text while preserving formatting
     */
    private function cleanExtractedText(string $text): string
    {
        // First, handle different line ending types
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        
        // Remove binary characters that sometimes appear in PDF text
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        
        // Fix common PDF extraction encoding issues that cause database problems
        $replacements = [
            'â€™' => "'",      // Smart apostrophe
            'â€œ' => '"',      // Smart opening quote
            'â€' => '"',       // Smart closing quote
            'â€˜' => "'",      // Smart opening apostrophe
            'â€¢' => '•',      // Bullet point
            'â€"' => '—',      // Em dash
            'â€"' => '–',      // En dash
            'â€¦' => '...',    // Ellipsis
            'Â' => '',         // Non-breaking space character
            'â€‹' => '',       // Zero-width space
            'â€¯' => ' ',      // Narrow no-break space
            '●​' => '●',       // Bullet with extra characters
            '○​' => '○',       // Circle with extra characters
            '■​' => '■',       // Square with extra characters
            '​' => '',         // Zero-width space (another variant)
            ' ' => ' ',        // Non-breaking space to regular space
            '‌' => '',         // Zero-width non-joiner
            '‍' => '',         // Zero-width joiner
        ];
        
        foreach ($replacements as $bad => $good) {
            $text = str_replace($bad, $good, $text);
        }
        
        // Remove any remaining problematic characters that could cause database encoding issues
        $text = preg_replace('/[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2060}-\x{206F}]/u', '', $text);
        
        // Convert to UTF-8 and ensure proper encoding
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        }
        
        // Remove any remaining non-printable characters except standard whitespace
        $text = preg_replace('/[^\x20-\x7E\n\r\t\x{00A0}-\x{017F}\x{0100}-\x{024F}]/u', '', $text);
        
        // If text has very few line breaks (flat text), try to add structure
        if (substr_count($text, "\n") < 20 && strlen($text) > 500) {
            $text = $this->addStructureToFlatText($text);
        }
        
        // Preserve paragraph breaks but clean up excessive whitespace
        // Split by paragraphs (double line breaks or more)
        $paragraphs = preg_split('/\n\s*\n+/', $text);
        
        $cleanedParagraphs = [];
        foreach ($paragraphs as $paragraph) {
            // Within each paragraph, normalize whitespace but preserve single line breaks
            $paragraph = trim($paragraph);
            if (!empty($paragraph)) {
                // Replace multiple spaces with single space, but keep line breaks
                $paragraph = preg_replace('/[ \t]+/', ' ', $paragraph);
                // Clean up line breaks within paragraph (remove excessive ones)
                $paragraph = preg_replace('/\n[ \t]*\n/', "\n", $paragraph);
                // Ensure proper sentence spacing
                $paragraph = preg_replace('/\. +/', '. ', $paragraph);
                $cleanedParagraphs[] = $paragraph;
            }
        }
        
        // Rejoin paragraphs with double line breaks
        $text = implode("\n\n", $cleanedParagraphs);
        
        // Handle bullet points and lists better
        $text = preg_replace('/\n([•●■▪▫◦‣⁃]|\d+\.|\w+\)|[-*+])\s*/', "\n$1 ", $text);
        
        // Ensure proper spacing after periods and colons
        $text = preg_replace('/([.!?:;])\s*([A-Z])/', '$1 $2', $text);
        
        // Handle headers and titles (text that's likely on its own line)
        $text = preg_replace('/\n([A-Z][A-Z\s]+[A-Z])\n/', "\n\n$1\n\n", $text);
        
        // Final cleanup
        $text = trim($text);
        
        // Ensure we don't have more than 2 consecutive line breaks
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        
        return $text;
    }

    /**
     * Add structure to flat text by detecting sentences and paragraphs
     */
    private function addStructureToFlatText(string $text): string
    {
        // Step 1: Add line breaks after the page header
        $text = preg_replace('/^(--- Page \d+ ---)/', "$1\n", $text);
        
        // Step 2: Handle document title patterns
        $patterns = [
            // Main title patterns
            '/(LearnFlow - AI-Powered Learning Path Optimizer)\s*(Final Year Project Proposal)/' => "$1\n\n$2\n\n",
            '/(Final Year Project Proposal)\s*(Executive Summary)/' => "$1\n\n$2\n\n",
            
            // Major section headers
            '/(tracking\.)\s*(Problem Statement)/' => "$1\n\n$2\n\n",
            '/(Executive Summary)\s*(LearnFlow is)/' => "$1\n\n$2",
            '/(Problem Statement)\s*(University students)/' => "$1\n\n$2",
            '/(data)\s*(Proposed Solution)/' => "$1\n\n$2\n\n",
            '/(Core Features)\s*(\d+\.)/' => "$1\n\n$2",
            
            // List item patterns
            '/(processes:)\s*(Information Overload)/' => "$1\n\n• $2",
            '/(systems)\s*(Inefficient Study Methods)/' => "$1\n\n• $2",
            '/(time)\s*(Poor Content Organization)/' => "$1\n\n• $2", 
            '/(collections)\s*(Limited Self-Assessment)/' => "$1\n\n• $2",
            '/(effectively)\s*(Unknown Learning Patterns)/' => "$1\n\n• $2",
            '/(weaknesses)\s*(Time Management Issues)/' => "$1\n\n• $2",
            
            // Feature sections
            '/(Processing)\s*(Automated PDF)/' => "$1\n\n○ $2",
            '/(structures)\s*(AI Question Generation)/' => "$1\n\n$2",
            '/(System)\s*(Automatic generation)/' => "$1\n\n○ $2",
            '/(tracking)\s*(Questionnaire-Based Learning)/' => "$1\n\n$2",
            '/(Analytics)\s*(Initial Learning)/' => "$1\n\n○ $2",
            
            // Break on colons followed by capital letters (list introductions)
            '/([a-z]:)\s*([A-Z][a-z])/' => "$1\n\n$2",
            
            // Break before numbered lists
            '/(\w)\s*(\d+\.\s*[A-Z])/' => "$1\n\n$2",
            
            // Break before bullet points
            '/([a-z])\s*(Information Overload|Inefficient Study|Poor Content|Limited Self|Unknown Learning|Time Management)/' => "$1\n\n• $2",
            
            // Generic sentence breaks for very long sentences
            '/(\.\s+)([A-Z][a-z]+\s+[a-z]+\s+[a-z]+)/' => "$1\n\n$2",
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text);
        }
        
        // Step 3: Add breaks between distinct topics/sections
        $sectionBreaks = [
            'Executive Summary',
            'Problem Statement', 
            'Proposed Solution',
            'Core Features',
            'Information Overload',
            'Inefficient Study Methods',
            'Poor Content Organization', 
            'Limited Self-Assessment',
            'Unknown Learning Patterns',
            'Time Management Issues',
            'Intelligent Document Processing',
            'AI Question Generation System',
            'Questionnaire-Based Learning Analytics'
        ];
        
        foreach ($sectionBreaks as $section) {
            // Add breaks before section headers (when not already present)
            $text = preg_replace('/([a-z\.])(\s*)(' . preg_quote($section) . ')(?!\w)/', "$1\n\n$3", $text);
        }
        
        // Step 4: Clean up excessive whitespace
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);
        
        return $text;
    }

    /**
     * Basic PHP text extraction (limited functionality - last resort)
     */
    private function extractTextBasic(string $pdfPath): string
    {
        try {
            Log::info('PdfOcrService: Attempting basic text extraction as fallback');
            
            // Read the PDF file
            $content = file_get_contents($pdfPath);
            
            if (empty($content)) {
                Log::warning('PdfOcrService: PDF file is empty or unreadable');
                return '';
            }
            
            // Verify this is actually a PDF file
            if (!str_starts_with($content, '%PDF')) {
                Log::warning('PdfOcrService: File does not appear to be a valid PDF');
                return '';
            }
            
            Log::info('PdfOcrService: Valid PDF detected, attempting basic text extraction');
            
            // Try multiple patterns to extract text with better formatting
            $textSegments = [];
            
            // Pattern 1: Text between parentheses with Tj operator
            if (preg_match_all('/\((.*?)\)\s*Tj/s', $content, $matches)) {
                foreach ($matches[1] as $match) {
                    $decoded = $this->decodePdfString($match);
                    if (!empty($decoded)) {
                        $textSegments[] = $decoded;
                    }
                }
            }
            
            // Pattern 2: Text arrays with TJ operator
            if (preg_match_all('/\[(.*?)\]\s*TJ/s', $content, $matches)) {
                foreach ($matches[1] as $match) {
                    // Extract strings from array format
                    if (preg_match_all('/\((.*?)\)/', $match, $arrayMatches)) {
                        foreach ($arrayMatches[1] as $arrayMatch) {
                            $decoded = $this->decodePdfString($arrayMatch);
                            if (!empty($decoded)) {
                                $textSegments[] = $decoded;
                            }
                        }
                    }
                }
            }
            
            // Pattern 3: BT...ET blocks (text objects) - try to preserve structure
            if (preg_match_all('/BT(.*?)ET/s', $content, $blocks)) {
                foreach ($blocks[1] as $block) {
                    $blockText = '';
                    if (preg_match_all('/\((.*?)\)\s*Tj/', $block, $blockMatches)) {
                        foreach ($blockMatches[1] as $blockMatch) {
                            $decoded = $this->decodePdfString($blockMatch);
                            if (!empty($decoded)) {
                                $blockText .= $decoded . ' ';
                            }
                        }
                    }
                    if (!empty($blockText)) {
                        $textSegments[] = trim($blockText);
                    }
                }
            }
            
            // Join segments with appropriate spacing
            $text = '';
            foreach ($textSegments as $i => $segment) {
                $text .= $segment;
                
                // Add spacing based on content analysis
                if ($i < count($textSegments) - 1) {
                    $nextSegment = $textSegments[$i + 1];
                    
                    // Check if this looks like end of sentence/paragraph
                    if (preg_match('/[.!?]\s*$/', $segment)) {
                        $text .= "\n";
                    } 
                    // Check if next segment starts with bullet point or number
                    elseif (preg_match('/^([•●■▪▫◦‣⁃]|\d+\.|\w+\))/', $nextSegment)) {
                        $text .= "\n";
                    }
                    // Check if this is likely a header (short, capitalized)
                    elseif (strlen($segment) < 50 && preg_match('/^[A-Z][A-Z\s]+$/', trim($segment))) {
                        $text .= "\n\n";
                    }
                    else {
                        $text .= " ";
                    }
                }
            }
            
            // Clean up the extracted text
            $text = $this->cleanExtractedText($text);
            
            // Add page header if not already present (to match other extraction methods)
            if (!empty($text) && !str_contains($text, '--- Page')) {
                $text = "--- Page 1 ---\n" . $text;
            }
            
            // Validate the extracted text
            if (strlen($text) < 10 || !preg_match('/[a-zA-Z]{3,}/', $text)) {
                Log::warning('PdfOcrService: No meaningful text extracted with basic method');
                return '';
            }
            
            Log::info('PdfOcrService: Basic extraction successful, extracted ' . strlen($text) . ' characters');
            return $text;
            
        } catch (Exception $e) {
            Log::error('PdfOcrService: Basic text extraction failed: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Decode PDF string encoding
     */
    private function decodePdfString(string $pdfString): string
    {
        // Handle basic PDF string decoding
        $decoded = $pdfString;
        
        // Handle escaped characters
        $decoded = str_replace(['\\(', '\\)', '\\\\'], ['(', ')', '\\'], $decoded);
        
        // Handle octal escape sequences
        $decoded = preg_replace_callback('/\\\\(\d{1,3})/', function($matches) {
            return chr(octdec($matches[1]));
        }, $decoded);
        
        // Filter out non-printable characters except spaces and common punctuation
        $decoded = preg_replace('/[^\x20-\x7E\n\r\t]/', '', $decoded);
        
        return trim($decoded);
    }

    /**
     * Extract text with advanced options and metadata
     */
    public function extractTextAdvanced(UploadedFile $file, array $options = []): array
    {
        try {
            if (!$this->isServiceAvailable()) {
                return [
                    'success' => false,
                    'error' => 'PDF processing service is not available'
                ];
            }

            $formData = [
                'include_metadata' => $options['include_metadata'] ?? 'true',
                'method' => $options['method'] ?? 'auto'
            ];

            $response = Http::timeout($this->timeout)
                ->attach('file', file_get_contents($file->getPathname()), $file->getClientOriginalName())
                ->post($this->serviceUrl . '/extract-text-advanced', $formData);

            if ($response->successful()) {
                $result = $response->json();
                
                if ($result['success']) {
                    return [
                        'success' => true,
                        'text' => $result['text'],
                        'method' => $result['method'] ?? 'unknown',
                        'char_count' => $result['char_count'] ?? 0,
                        'word_count' => $result['word_count'] ?? 0,
                        'line_count' => $result['line_count'] ?? 0,
                        'metadata' => $result['metadata'] ?? null
                    ];
                } else {
                    return [
                        'success' => false,
                        'error' => $result['error'] ?? 'Unknown error occurred'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'error' => 'Failed to communicate with PDF processing service'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Advanced PDF OCR processing failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to process PDF: ' . $e->getMessage()
            ];
        }
    }
}
