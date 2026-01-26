<?php

namespace App\Console\Commands;

use App\Models\Note;
use App\Services\ContentStructureService;
use Illuminate\Console\Command;

class ProcessPdfStructure extends Command
{
    protected $signature = 'notes:process-structure {id?}';
    protected $description = 'Process PDF notes to add structured content';

    public function handle()
    {
        $noteId = $this->argument('id');
        
        if ($noteId) {
            $note = Note::find($noteId);
            if (!$note) {
                $this->error("Note with ID {$noteId} not found.");
                return 1;
            }
            $notes = collect([$note]);
        } else {
            $notes = Note::where('is_pdf_note', true)
                         ->whereNotNull('extracted_text')
                         ->get();
        }

        if ($notes->isEmpty()) {
            $this->info('No PDF notes found to process.');
            return 0;
        }

        $service = new ContentStructureService();
        $processed = 0;

        foreach ($notes as $note) {
            $this->info("Processing note: {$note->title}");
            
            try {
                $result = $service->processPdfContent($note->extracted_text);
                $note->update($result);
                $processed++;
                $this->info("✓ Processed successfully");
            } catch (\Exception $e) {
                $this->error("✗ Failed to process: " . $e->getMessage());
            }
        }

        $this->info("Processed {$processed} notes successfully.");
        return 0;
    }
}
