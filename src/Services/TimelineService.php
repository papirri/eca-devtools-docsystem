<?php

declare(strict_types=1);

namespace Devtools\DocSystem\Services;

use Devtools\DocSystem\Models\DocEvent;
use Illuminate\Support\Facades\Auth;

class TimelineService
{
    /**
     * Record an event for a given doc page.
     *
     * @param int         $docPageId
     * @param string      $eventType  One of the event_types in config
     * @param array<string,mixed> $metadata   Extra context
     */
    public function record(int $docPageId, string $eventType, array $metadata = []): DocEvent
    {
        return DocEvent::create([
            'doc_page_id'  => $docPageId,
            'event_type'   => $eventType,
            'performed_by' => Auth::check() ? Auth::user()?->name ?? Auth::user()?->email : null,
            'metadata'     => $metadata ?: null,
        ]);
    }
}
