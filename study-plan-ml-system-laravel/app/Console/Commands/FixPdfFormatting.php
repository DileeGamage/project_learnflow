<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Note;
use App\Services\PdfOcrService;

class FixPdfFormatting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notes:fix-pdf-formatting {--note-id= : Fix specific note ID} {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix PDF text formatting for existing notes by re-processing extracted text';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('PDF Text Formatting Fix Tool');
        $this->info('===========================');
        $this->newLine();

        $noteId = $this->option('note-id');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Get notes to process
        if ($noteId) {
            $notes = Note::where('id', $noteId)->where('is_pdf_note', true)->get();
            if ($notes->isEmpty()) {
                $this->error("No PDF note found with ID: {$noteId}");
                return Command::FAILURE;
            }
        } else {
            $notes = Note::where('is_pdf_note', true)
                         ->whereNotNull('extracted_text')
                         ->get();
        }

        if ($notes->isEmpty()) {
            $this->warn('No PDF notes found to process.');
            return Command::SUCCESS;
        }

        $this->info("Found {$notes->count()} PDF notes to process");
        $this->newLine();

        $processedCount = 0;
        $errorCount = 0;

        foreach ($notes as $note) {
            $this->info("Processing Note #{$note->id}: {$note->title}");
            
            try {
                // Show current formatting issue
                $originalText = $note->extracted_text;
                $this->line("Original text length: " . strlen($originalText) . " characters");
                
                // Count line breaks in original
                $originalLines = substr_count($originalText, "\n");
                $this->line("Original line breaks: {$originalLines}");
                
                // Show preview of original (first 200 chars)
                $this->line("Original preview: " . substr(str_replace("\n", "\\n", $originalText), 0, 200) . "...");
                
                if (!$dryRun) {
                    // Re-process the text with improved formatting
                    $pdfService = new PdfOcrService();
                    $improvedText = $this->improveTextFormatting($originalText);
                    
                    // Update the note
                    $note->extracted_text = $improvedText;
                    $note->save();
                    
                    $this->info("✅ Updated note with improved formatting");
                } else {
                    $improvedText = $this->improveTextFormatting($originalText);
                    $this->info("Would update with improved formatting");
                }
                
                // Show improved stats
                $improvedLines = substr_count($improvedText, "\n");
                $this->line("Improved line breaks: {$improvedLines}");
                $this->line("Improved preview: " . substr(str_replace("\n", "\\n", $improvedText), 0, 200) . "...");
                
                $processedCount++;
                
            } catch (\Exception $e) {
                $this->error("❌ Failed to process note #{$note->id}: " . $e->getMessage());
                $errorCount++;
            }
            
            $this->newLine();
        }

        $this->info('Processing Summary:');
        $this->info("✅ Successfully processed: {$processedCount} notes");
        if ($errorCount > 0) {
            $this->error("❌ Errors: {$errorCount} notes");
        }

        if ($dryRun) {
            $this->warn('This was a dry run. Use --no-dry-run to actually apply changes.');
        }

        return Command::SUCCESS;
    }

    /**
     * Improve text formatting using the same logic as the enhanced PDF service
     */
    private function improveTextFormatting(string $text): string
    {
        // Apply the same cleaning logic as the enhanced PdfOcrService
        
        // First, handle different line ending types
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        
        // Remove binary characters and problematic Unicode characters
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        
        // Fix common PDF extraction encoding issues
        $replacements = [
            'â€™' => "'",
            'â€œ' => '"',
            'â€' => '"',
            'â€˜' => "'",
            'â€¢' => '•',
            'â€"' => '—',
            'â€"' => '–',
            'â€¦' => '...',
            'Â' => '',
            'â€‹' => '', // Zero-width space
            'â€¯' => ' ', // Narrow no-break space
            '●​' => '•', // Bullet with extra characters - change to simple bullet
            '○​' => '○', // Circle with extra characters
            '■​' => '■', // Square with extra characters
            // Remove problematic bullet characters that cause encoding issues
            '●' => '•', // Replace filled circle with standard bullet
            '○' => '•', // Replace circle with standard bullet  
            '■' => '•', // Replace square with standard bullet
            '▪' => '•', // Replace small square with standard bullet
            '▫' => '•', // Replace white square with standard bullet
            '◦' => '•', // Replace white bullet with standard bullet
            '‣' => '•', // Replace triangular bullet with standard bullet
            '⁃' => '•', // Replace hyphen bullet with standard bullet
        ];
        
        foreach ($replacements as $bad => $good) {
            $text = str_replace($bad, $good, $text);
        }
        
        // Remove any remaining problematic characters that might cause database issues
        $text = preg_replace('/[^\x20-\x7E\n\r\t]/u', '', $text);
        
        // Convert to UTF-8 and ensure proper encoding
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        }
        
        // If the text is all on one line (old format), try to add structure
        if (substr_count($text, "\n") < 20 && strlen($text) > 500) {
            // This is likely the old format with no line breaks
            $this->line("Detected flat text format, applying structure...");
            $text = $this->addStructureToFlatText($text);
        }
        
        // Preserve paragraph breaks but clean up excessive whitespace
        $paragraphs = preg_split('/\n\s*\n+/', $text);
        
        $cleanedParagraphs = [];
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (!empty($paragraph)) {
                // Within each paragraph, normalize whitespace but preserve single line breaks
                $paragraph = preg_replace('/[ \t]+/', ' ', $paragraph);
                // Clean up line breaks within paragraph
                $paragraph = preg_replace('/\n[ \t]*\n/', "\n", $paragraph);
                // Ensure proper sentence spacing
                $paragraph = preg_replace('/\. +/', '. ', $paragraph);
                $cleanedParagraphs[] = $paragraph;
            }
        }
        
        // Rejoin paragraphs with double line breaks
        $text = implode("\n\n", $cleanedParagraphs);
        
        // Handle bullet points and lists better
        $text = preg_replace('/\n([•]|\d+\.|\w+\)|[-*+])\s*/', "\n$1 ", $text);
        
        // Ensure proper spacing after periods and colons
        $text = preg_replace('/([.!?:;])\s*([A-Z])/', '$1 $2', $text);
        
        // Handle headers and titles
        $text = preg_replace('/\n([A-Z][A-Z\s]+[A-Z])\n/', "\n\n$1\n\n", $text);
        
        // Final cleanup
        $text = trim($text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        
        return $text;
    }

    /**
     * Add structure to flat text by detecting sentences and paragraphs
     */
    private function addStructureToFlatText(string $text): string
    {
        // First, handle common document structure patterns
        $patterns = [
            // Project/document titles and headers
            '/(\w+)\s+(Final Year Project Proposal)/' => "$1\n\n$2\n\n",
            '/(\w+)\s+(Executive Summary)/' => "$1\n\n$2\n\n",
            '/(\w+)\s+(Problem Statement)/' => "$1\n\n$2\n\n",
            
            // Section headers that should have breaks before them
            '/([\.\!\?])\s*(Executive Summary)/' => "$1\n\n$2\n\n",
            '/([\.\!\?])\s*(Problem Statement)/' => "$1\n\n$2\n\n",
            '/([\.\!\?])\s*(University students)/' => "$1\n\n$2",
            '/([\.\!\?])\s*(Information Overload)/' => "$1\n\n$2",
            '/([\.\!\?])\s*(Inefficient Study Methods)/' => "$1\n\n$2",
            '/([\.\!\?])\s*(Poor Content Organization)/' => "$1\n\n$2",
            '/([\.\!\?])\s*(Limited Self-Assessment)/' => "$1\n\n$2",
            '/([\.\!\?])\s*(Unknown Learning Patterns)/' => "$1\n\n$2",
            '/([\.\!\?])\s*(Time Management Issues)/' => "$1\n\n$2",
            '/([\.\!\?])\s*(Proposed Solution)/' => "$1\n\n$2\n\n",
            '/([\.\!\?])\s*(Core Features)/' => "$1\n\n$2\n\n",
            '/([\.\!\?])\s*(Intelligent Document Processing)/' => "$1\n\n$2",
            '/([\.\!\?])\s*(AI Question Generation System)/' => "$1\n\n$2",
            '/([\.\!\?])\s*(Questionnaire-Based Learning Analytics)/' => "$1\n\n$2",
            '/([\.\!\?])\s*(Visual Learning Path Analytics)/' => "$1\n\n$2",
            '/([\.\!\?])\s*(Performance Correlation Analysis)/' => "$1\n\n$2",
            '/([\.\!\?])\s*(Enhanced Questionnaire System)/' => "$1\n\n$2",
            
            // Bullet points and numbered lists
            '/([\.\!\?])\s*([•])/' => "$1\n\n$2",
            '/([\.\!\?])\s*(\d+\.)/' => "$1\n\n$2",
            
            // Break before numbered features or points
            '/([\.\!\?])\s*([1-9]\.\s*[A-Z])/' => "$1\n\n$2",
            
            // Special patterns for this document
            '/([a-z])\s+(LearnFlow is)/' => "$1\n\n$2",
            '/([a-z])\s+(The system)/' => "$1\n\n$2",
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text);
        }
        
        // Add line breaks after sentences that end with periods followed by capital letters
        $text = preg_replace('/\. ([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*:)/', ".\n\n$1", $text);
        $text = preg_replace('/\. ([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*\s+face)/', ".\n\n$1", $text);
        
        // Handle specific document sections
        $text = preg_replace('/tracking\.\s*Problem Statement/', "tracking.\n\nProblem Statement", $text);
        $text = preg_replace('/processes:\s*([•])/', "processes:\n\n$1", $text);
        $text = preg_replace('/systems\s*([•])/', "systems\n\n$1", $text);
        $text = preg_replace('/time\s*([•])/', "time\n\n$1", $text);
        $text = preg_replace('/collections\s*([•])/', "collections\n\n$1", $text);
        $text = preg_replace('/effectively\s*([•])/', "effectively\n\n$1", $text);
        $text = preg_replace('/weaknesses\s*([•])/', "weaknesses\n\n$1", $text);
        $text = preg_replace('/data\s*(Proposed Solution)/', "data\n\n$1", $text);
        
        // Clean up excessive whitespace that might have been created
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        
        return $text;
    }
}
