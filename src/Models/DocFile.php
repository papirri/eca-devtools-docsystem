<?php

declare(strict_types=1);

namespace Devtools\DocSystem\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocFile extends Model
{
    protected $fillable = [
        'doc_page_id',
        'name',
        'extension',
        'is_text',
    ];

    protected $casts = [
        'is_text' => 'boolean',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(DocPage::class, 'doc_page_id');
    }

    public function fileVersions(): HasMany
    {
        return $this->hasMany(DocFileVersion::class)->orderByDesc('created_at');
    }

    public function latestVersion(): ?DocFileVersion
    {
        return $this->fileVersions()->first();
    }
}
