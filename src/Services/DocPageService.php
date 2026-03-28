<?php

declare(strict_types=1);

namespace Devtools\DocSystem\Services;

use Devtools\DocSystem\Models\DocPage;

class DocPageService
{
    /**
     * Retrieve or create a DocPage record for the given URL path.
     */
    public function findOrCreateByPath(string $urlPath): DocPage
    {
        return DocPage::firstOrCreate(
            ['url_path' => $urlPath],
            ['title' => $urlPath]
        );
    }
}
