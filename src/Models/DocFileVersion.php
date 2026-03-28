<?php

declare(strict_types=1);

namespace Devtools\DocSystem\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DocFileVersion extends Model
{
    protected $fillable = [
        'doc_file_id',
        'version_number',
        'disk_path',
        'original_name',
        'size',
        'mime_type',
        'uploaded_by',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function docFile(): BelongsTo
    {
        return $this->belongsTo(DocFile::class, 'doc_file_id');
    }

    /** Returns a public URL to download the file. */
    public function downloadUrl(): string
    {
        return Storage::disk('public')->url($this->disk_path);
    }

    /** Returns raw text content for diff (only for text files). */
    public function readText(): string
    {
        return Storage::disk('public')->get($this->disk_path) ?? '';
    }

    /** Human-readable file size. */
    public function humanSize(): string
    {
        $bytes = $this->size;
        if ($bytes < 1024) {
            return "{$bytes} B";
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return round($bytes / 1048576, 1) . ' MB';
    }
}
