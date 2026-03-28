<?php

declare(strict_types=1);

namespace Devtools\DocSystem\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocVersion extends Model
{
    protected $fillable = [
        'doc_page_id',
        'version_number',
        'title',
        'description',
        'created_by',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(DocPage::class, 'doc_page_id');
    }
}
