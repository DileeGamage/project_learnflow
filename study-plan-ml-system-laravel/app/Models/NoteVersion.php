<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteVersion extends Model
{
    protected $fillable = [
        'note_id',
        'title',
        'content',
        'subject_area',
        'tags',
        'extracted_text',
        'version_number',
        'change_summary',
        'created_by'
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    /**
     * Get the note that owns this version
     */
    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    /**
     * Get the user who created this version
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get formatted tags for display
     */
    public function getTagsListAttribute()
    {
        return $this->tags ? implode(', ', $this->tags) : '';
    }

    /**
     * Get the content to display (either regular content or extracted text)
     */
    public function getDisplayContentAttribute()
    {
        return $this->extracted_text ?: $this->content;
    }

    /**
     * Get word count for this version
     */
    public function getWordCountAttribute()
    {
        return str_word_count(strip_tags($this->display_content));
    }

    /**
     * Get character count for this version
     */
    public function getCharacterCountAttribute()
    {
        return strlen(strip_tags($this->display_content));
    }
}
