<?php

declare(strict_types=1);

namespace Devtools\DocSystem\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocPage extends Model
{
    protected $fillable = [
        'url_path',
        'query_string',
        'title',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(DocVersion::class)->orderByDesc('created_at');
    }

    public function events(): HasMany
    {
        return $this->hasMany(DocEvent::class)->orderByDesc('created_at');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(DocNote::class)->orderByDesc('created_at');
    }

    public function files(): HasMany
    {
        return $this->hasMany(DocFile::class)->orderByDesc('created_at');
    }
}
