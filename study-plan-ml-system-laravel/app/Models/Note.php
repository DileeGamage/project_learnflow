<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'subject_area',
        'user_id',
        'tags',
        'is_favorite',
        'pdf_path',
        'extracted_text',
        'is_pdf_note',
        'structured_content',
        'content_outline',
        'content_sections',
        'document_type'
    ];

    protected $casts = [
        'tags' => 'array',
        'is_favorite' => 'boolean',
        'is_pdf_note' => 'boolean',
        'structured_content' => 'array',
        'content_outline' => 'array',
        'content_sections' => 'array',
    ];

    /**
     * Get the user who owns this note
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all quizzes for this note
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    /**
     * Get all versions for this note
     */
    public function versions(): HasMany
    {
        return $this->hasMany(NoteVersion::class)->orderBy('version_number', 'desc');
    }

    /**
     * Get the latest version
     */
    public function latestVersion()
    {
        return $this->versions()->latest()->first();
    }

    /**
     * Get the current version number
     */
    public function getCurrentVersionNumber()
    {
        $latestVersion = $this->versions()->latest('version_number')->first();
        return $latestVersion ? $latestVersion->version_number + 1 : 1;
    }

    /**
     * Get the content for editing (latest version or original if no versions)
     */
    public function getEditableContent()
    {
        $latestVersion = $this->latestVersion();
        return $latestVersion ? $latestVersion->content : $this->content;
    }

    /**
     * Get the title for editing (latest version or original if no versions)
     */
    public function getEditableTitle()
    {
        $latestVersion = $this->latestVersion();
        return $latestVersion ? $latestVersion->title : $this->title;
    }

    /**
     * Get the subject area for editing (latest version or original if no versions)
     */
    public function getEditableSubjectArea()
    {
        $latestVersion = $this->latestVersion();
        return $latestVersion ? $latestVersion->subject_area : $this->subject_area;
    }

    /**
     * Get the tags for editing (latest version or original if no versions)
     */
    public function getEditableTags()
    {
        $latestVersion = $this->latestVersion();
        return $latestVersion ? $latestVersion->tags : $this->tags;
    }

    /**
     * Get the extracted text for editing (latest version or original if no versions)
     */
    public function getEditableExtractedText()
    {
        $latestVersion = $this->latestVersion();
        return $latestVersion ? $latestVersion->extracted_text : $this->extracted_text;
    }

    /**
     * Create a new version with new content (doesn't update the original note)
     */
    public function createVersion($title, $content, $subjectArea, $tags, $extractedText = null, $changeSummary = null, $userId = null)
    {
        return $this->versions()->create([
            'title' => $title,
            'content' => $content,
            'subject_area' => $subjectArea,
            'tags' => $tags,
            'extracted_text' => $extractedText,
            'version_number' => $this->getCurrentVersionNumber(),
            'change_summary' => $changeSummary,
            'created_by' => $userId
        ]);
    }

    // Get the content to display (original content always for index)
    public function getDisplayContentAttribute()
    {
        if ($this->is_pdf_note) {
            // If we have structured content, format it nicely
            if ($this->hasStructuredContent()) {
                return $this->getFormattedStructuredContent();
            }
            // Otherwise, format the raw extracted text with proper spacing
            return $this->getFormattedExtractedText();
        }
        return $this->content;
    }

    // Check if note has structured content
    public function hasStructuredContent(): bool
    {
        return !empty($this->structured_content) && is_array($this->structured_content);
    }

    // Get formatted structured content as HTML
    public function getFormattedStructuredContent(): string
    {
        if (!$this->hasStructuredContent()) {
            return $this->getFormattedExtractedText();
        }

        $html = '<div class="structured-content">';
        
        foreach ($this->structured_content as $section) {
            $html .= '<div class="content-section mb-4">';
            $html .= '<h3 class="section-title text-primary mb-3"><i class="fas fa-bookmark"></i> ' . htmlspecialchars($section['title']) . '</h3>';
            
            // Add section content
            if (!empty($section['content'])) {
                foreach ($section['content'] as $content) {
                    if ($content['type'] === 'paragraph') {
                        $html .= '<p class="mb-2">' . htmlspecialchars($content['text']) . '</p>';
                    } elseif ($content['type'] === 'bullet') {
                        $html .= '<ul class="mb-2"><li>' . htmlspecialchars($content['text']) . '</li></ul>';
                    }
                }
            }
            
            // Add subsections
            if (!empty($section['subsections'])) {
                foreach ($section['subsections'] as $subsection) {
                    $html .= '<div class="content-subsection ms-3 mb-3">';
                    $html .= '<h4 class="subsection-title text-secondary mb-2"><i class="fas fa-arrow-right"></i> ' . htmlspecialchars($subsection['title']) . '</h4>';
                    
                    if (!empty($subsection['content'])) {
                        foreach ($subsection['content'] as $content) {
                            if ($content['type'] === 'paragraph') {
                                $html .= '<p class="mb-2 ms-2">' . htmlspecialchars($content['text']) . '</p>';
                            } elseif ($content['type'] === 'bullet') {
                                $html .= '<ul class="mb-2 ms-2"><li>' . htmlspecialchars($content['text']) . '</li></ul>';
                            }
                        }
                    }
                    
                    $html .= '</div>';
                }
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    // Get formatted extracted text with proper spacing and preserved formatting
    public function getFormattedExtractedText(): string
    {
        if (empty($this->extracted_text)) {
            return '<p class="text-muted">No content available.</p>';
        }

        $text = $this->extracted_text;
        
        // Convert text to HTML while preserving line breaks and formatting
        $html = '<div class="formatted-content">';
        
        // Split by double line breaks to get paragraphs
        $paragraphs = preg_split('/\n\s*\n/', $text);
        
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (empty($paragraph)) continue;
            
            // Handle different types of content
            if ($this->isHeader($paragraph)) {
                // This looks like a header
                $level = $this->getHeaderLevel($paragraph);
                $html .= "<h{$level} class=\"mt-4 mb-3\">" . htmlspecialchars($paragraph) . "</h{$level}>";
            } elseif ($this->isList($paragraph)) {
                // This looks like a list
                $html .= $this->formatList($paragraph);
            } else {
                // Regular paragraph - preserve internal line breaks
                $formattedParagraph = htmlspecialchars($paragraph);
                // Convert single line breaks to <br> tags for better display
                $formattedParagraph = nl2br($formattedParagraph);
                $html .= '<p class="mb-3">' . $formattedParagraph . '</p>';
            }
        }
        
        $html .= '</div>';
        
        return $html;
    }

    // Check if text looks like a header
    private function isHeader(string $text): bool
    {
        // Headers are usually short, might be all caps, and don't end with periods
        $text = trim($text);
        return (
            strlen($text) < 100 && 
            !str_ends_with($text, '.') && 
            (ctype_upper($text) || preg_match('/^[A-Z][a-zA-Z\s]+$/', $text))
        );
    }

    // Get header level based on content
    private function getHeaderLevel(string $text): int
    {
        if (strlen($text) < 30) return 3;
        if (strlen($text) < 50) return 4;
        return 5;
    }

    // Check if text looks like a list
    private function isList(string $text): bool
    {
        return preg_match('/^([•●■▪▫◦‣⁃]|\d+\.|\w+\))/m', $text);
    }

    // Format list content
    private function formatList(string $text): string
    {
        $lines = explode("\n", $text);
        $html = '<ul class="mb-3">';
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Remove bullet points/numbers from the beginning
            $line = preg_replace('/^([•●■▪▫◦‣⁃]|\d+\.|\w+\))\s*/', '', $line);
            $html .= '<li>' . htmlspecialchars($line) . '</li>';
        }
        
        $html .= '</ul>';
        return $html;
    }

    // Get content outline for display
    public function getContentOutlineAttribute()
    {
        return $this->content_outline ?? [];
    }

    // Get the current working content (latest version or original)
    public function getCurrentContentAttribute()
    {
        $latestVersion = $this->latestVersion();
        if ($latestVersion) {
            if ($this->is_pdf_note) {
                // For PDF notes, return formatted content if it's structured
                if ($this->hasStructuredContent()) {
                    return $this->getFormattedStructuredContent();
                } else {
                    return $this->getFormattedExtractedText();
                }
            }
            return $latestVersion->content;
        }
        
        // No versions, return the original content
        if ($this->is_pdf_note) {
            if ($this->hasStructuredContent()) {
                return $this->getFormattedStructuredContent();
            } else {
                return $this->getFormattedExtractedText();
            }
        }
        return $this->content;
    }

    // Get the PDF URL if it exists
    public function getPdfUrlAttribute()
    {
        return $this->pdf_path ? asset('storage/' . $this->pdf_path) : null;
    }

    // Get formatted tags for display
    public function getTagsListAttribute()
    {
        return $this->tags ? implode(', ', $this->tags) : '';
    }
}
