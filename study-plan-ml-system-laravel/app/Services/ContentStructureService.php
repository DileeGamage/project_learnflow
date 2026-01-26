<?php

namespace App\Services;

class ContentStructureService
{
    /**
     * Process PDF content into structured format
     */
    public function processPdfContent(string $content): array
    {
        $lines = explode("\n", $content);
        $structuredContent = $this->parseContentStructure($lines);
        
        return [
            'structured_content' => $structuredContent,
            'content_outline' => $this->generateOutline($structuredContent),
            'content_sections' => $this->extractSections($structuredContent),
            'document_type' => $this->detectDocumentType($content)
        ];
    }

    /**
     * Parse content into hierarchical structure
     */
    private function parseContentStructure(array $lines): array
    {
        $structure = [];
        $currentSection = null;
        $currentSubsection = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Detect headings (various patterns)
            if ($this->isHeading($line)) {
                $level = $this->getHeadingLevel($line);
                $title = $this->cleanHeading($line);
                
                if ($level === 1) {
                    $currentSection = [
                        'type' => 'section',
                        'level' => 1,
                        'title' => $title,
                        'content' => [],
                        'subsections' => []
                    ];
                    $structure[] = &$currentSection;
                    $currentSubsection = null;
                } elseif ($level === 2 && $currentSection) {
                    $currentSubsection = [
                        'type' => 'subsection',
                        'level' => 2,
                        'title' => $title,
                        'content' => []
                    ];
                    $currentSection['subsections'][] = &$currentSubsection;
                } else {
                    // Handle as regular content if no proper section
                    $this->addContent($structure, $currentSection, $currentSubsection, $line);
                }
            } elseif ($this->isBulletPoint($line)) {
                $this->addBulletPoint($structure, $currentSection, $currentSubsection, $line);
            } else {
                $this->addContent($structure, $currentSection, $currentSubsection, $line);
            }
        }
        
        return $structure;
    }

    /**
     * Check if line is a heading
     */
    private function isHeading(string $line): bool
    {
        // Check for various heading patterns
        return preg_match('/^(#+\s+|[A-Z][^.]*:|\d+\.\s+[A-Z]|[A-Z\s]+$)/', $line) ||
               (strlen($line) < 100 && preg_match('/^[A-Z]/', $line) && !preg_match('/[.!?]$/', $line));
    }

    /**
     * Get heading level
     */
    private function getHeadingLevel(string $line): int
    {
        if (preg_match('/^(#+)\s+/', $line, $matches)) {
            return strlen($matches[1]);
        }
        
        if (preg_match('/^[A-Z\s]+$/', $line)) {
            return 1; // All caps = main heading
        }
        
        if (preg_match('/^\d+\.\s+/', $line)) {
            return 2; // Numbered heading = subsection
        }
        
        return 2; // Default to subsection level
    }

    /**
     * Clean heading text
     */
    private function cleanHeading(string $line): string
    {
        // Remove markdown markers
        $line = preg_replace('/^#+\s+/', '', $line);
        // Remove trailing colons
        $line = rtrim($line, ':');
        // Remove numbers
        $line = preg_replace('/^\d+\.\s+/', '', $line);
        
        return trim($line);
    }

    /**
     * Check if line is a bullet point
     */
    private function isBulletPoint(string $line): bool
    {
        return preg_match('/^[\s]*[-•*]\s+/', $line) || 
               preg_match('/^[\s]*\d+\.\s+/', $line) ||
               preg_match('/^[\s]*[a-z]\)\s+/', $line);
    }

    /**
     * Add bullet point to structure
     */
    private function addBulletPoint(array &$structure, ?array &$currentSection, ?array &$currentSubsection, string $line): void
    {
        $bulletPoint = [
            'type' => 'bullet',
            'text' => preg_replace('/^[\s]*[-•*\d+\.a-z\)]\s+/', '', $line)
        ];

        if ($currentSubsection) {
            $currentSubsection['content'][] = $bulletPoint;
        } elseif ($currentSection) {
            $currentSection['content'][] = $bulletPoint;
        } else {
            // Create a general section if none exists
            if (empty($structure)) {
                $structure[] = [
                    'type' => 'section',
                    'level' => 1,
                    'title' => 'Document Content',
                    'content' => [],
                    'subsections' => []
                ];
            }
            $structure[0]['content'][] = $bulletPoint;
        }
    }

    /**
     * Add regular content to structure
     */
    private function addContent(array &$structure, ?array &$currentSection, ?array &$currentSubsection, string $line): void
    {
        $contentItem = [
            'type' => 'paragraph',
            'text' => $line
        ];

        if ($currentSubsection) {
            $currentSubsection['content'][] = $contentItem;
        } elseif ($currentSection) {
            $currentSection['content'][] = $contentItem;
        } else {
            // Create a general section if none exists
            if (empty($structure)) {
                $structure[] = [
                    'type' => 'section',
                    'level' => 1,
                    'title' => 'Document Content',
                    'content' => [],
                    'subsections' => []
                ];
            }
            $structure[0]['content'][] = $contentItem;
        }
    }

    /**
     * Generate outline from structured content
     */
    private function generateOutline(array $structure): array
    {
        $outline = [];
        
        foreach ($structure as $section) {
            $outlineItem = [
                'title' => $section['title'],
                'level' => $section['level'],
                'subsections' => []
            ];
            
            if (!empty($section['subsections'])) {
                foreach ($section['subsections'] as $subsection) {
                    $outlineItem['subsections'][] = [
                        'title' => $subsection['title'],
                        'level' => $subsection['level']
                    ];
                }
            }
            
            $outline[] = $outlineItem;
        }
        
        return $outline;
    }

    /**
     * Extract sections for easy access
     */
    private function extractSections(array $structure): array
    {
        $sections = [];
        
        foreach ($structure as $section) {
            $sections[] = [
                'title' => $section['title'],
                'content_count' => count($section['content']),
                'subsection_count' => count($section['subsections'] ?? [])
            ];
        }
        
        return $sections;
    }

    /**
     * Detect document type based on content
     */
    private function detectDocumentType(string $content): string
    {
        if (preg_match('/table of contents|index/i', $content)) {
            return 'manual';
        } elseif (preg_match('/chapter|lesson|unit/i', $content)) {
            return 'educational';
        } elseif (preg_match('/abstract|introduction|conclusion|references/i', $content)) {
            return 'academic';
        } elseif (preg_match('/executive summary|overview|objectives/i', $content)) {
            return 'report';
        }
        
        return 'document';
    }

    /**
     * Format structured content as HTML for display
     */
    public function formatStructuredContentAsHtml(array $structuredContent): string
    {
        $html = '<div class="structured-content">';
        
        foreach ($structuredContent as $section) {
            $html .= '<div class="content-section">';
            $html .= '<h2 class="section-title">' . htmlspecialchars($section['title']) . '</h2>';
            
            // Add section content
            foreach ($section['content'] as $content) {
                if ($content['type'] === 'paragraph') {
                    $html .= '<p>' . htmlspecialchars($content['text']) . '</p>';
                } elseif ($content['type'] === 'bullet') {
                    $html .= '<ul><li>' . htmlspecialchars($content['text']) . '</li></ul>';
                }
            }
            
            // Add subsections
            if (!empty($section['subsections'])) {
                foreach ($section['subsections'] as $subsection) {
                    $html .= '<div class="content-subsection">';
                    $html .= '<h3 class="subsection-title">' . htmlspecialchars($subsection['title']) . '</h3>';
                    
                    foreach ($subsection['content'] as $content) {
                        if ($content['type'] === 'paragraph') {
                            $html .= '<p>' . htmlspecialchars($content['text']) . '</p>';
                        } elseif ($content['type'] === 'bullet') {
                            $html .= '<ul><li>' . htmlspecialchars($content['text']) . '</li></ul>';
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
}
