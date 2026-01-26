<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PdfOcrService;
use Illuminate\Support\Facades\Storage;

class TestPdfExtraction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:pdf-extraction {--file= : Optional PDF file name in uploads directory}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test PDF text extraction functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing PDF Extraction Service');
        $this->info('==============================');
        $this->newLine();

        $pdfService = new PdfOcrService();
        
        // Check uploads directory
        $uploadsDir = public_path('uploads');
        if (!is_dir($uploadsDir)) {
            $this->info('Creating uploads directory...');
            mkdir($uploadsDir, 0755, true);
        }

        // Get file from option or find PDF files
        $fileName = $this->option('file');
        
        if ($fileName) {
            $testFile = $uploadsDir . '/' . $fileName;
            if (!file_exists($testFile)) {
                $this->error("File not found: {$fileName}");
                return Command::FAILURE;
            }
            $pdfFiles = [$testFile];
        } else {
            // Check for any PDF files in uploads
            $pdfFiles = glob($uploadsDir . '/*.pdf');
        }

        if (empty($pdfFiles)) {
            $this->warn('No PDF files found in uploads directory.');
            $this->info("Please place a PDF file in: {$uploadsDir}");
            $this->info('Then run: php artisan test:pdf-extraction --file=yourfile.pdf');
            return Command::SUCCESS;
        }

        $this->info('Found PDF files:');
        foreach ($pdfFiles as $pdfFile) {
            $this->line('- ' . basename($pdfFile));
        }
        $this->newLine();

        // Test extraction on first (or specified) PDF file
        $testFile = $pdfFiles[0];
        $this->info('Testing extraction on: ' . basename($testFile));
        $this->info('File size: ' . number_format(filesize($testFile)) . ' bytes');
        $this->newLine();

        try {
            $this->info('Starting text extraction...');
            $extractedText = $pdfService->extractText($testFile);

            if (!empty($extractedText)) {
                $this->info('✅ Extraction successful!');
                $this->info('Extracted text length: ' . number_format(strlen($extractedText)) . ' characters');
                $this->newLine();
                
                $this->info('First 500 characters:');
                $this->info('=====================');
                $preview = substr($extractedText, 0, 500);
                $this->line($preview);
                if (strlen($extractedText) > 500) {
                    $this->line('...');
                }
                
                // Show some statistics
                $wordCount = str_word_count($extractedText);
                $lineCount = substr_count($extractedText, "\n") + 1;
                
                $this->newLine();
                $this->info("Statistics:");
                $this->info("- Words: " . number_format($wordCount));
                $this->info("- Lines: " . number_format($lineCount));
                $this->info("- Characters: " . number_format(strlen($extractedText)));
                
            } else {
                $this->error('❌ No text extracted from PDF');
                $this->warn('This could mean:');
                $this->warn('- The PDF contains only images (scanned document)');
                $this->warn('- The PDF is encrypted or password protected');
                $this->warn('- The PDF has a complex format not supported by basic extraction');
            }
        } catch (\Exception $e) {
            $this->error('❌ Error during extraction: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // Test library status
        $this->newLine();
        $this->info('Library Status:');
        $this->info('===============');

        if (class_exists('Smalot\PdfParser\Parser')) {
            $this->info('✅ smalot/pdfparser library is available');
        } else {
            $this->error('❌ smalot/pdfparser library is NOT available');
            $this->warn('   Run: composer require smalot/pdfparser');
        }

        if (is_writable($uploadsDir)) {
            $this->info('✅ Uploads directory is writable');
        } else {
            $this->error('❌ Uploads directory is NOT writable');
        }

        $this->newLine();
        $this->info('Test completed successfully!');
        
        return Command::SUCCESS;
    }
}
