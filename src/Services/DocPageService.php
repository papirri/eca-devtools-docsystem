<?php

declare(strict_types=1);

namespace Devtools\DocSystem\Services;

use Devtools\DocSystem\Models\DocPage;

class DocPageService
{
    /**
     * Retrieve or create a DocPage record for the given URL path + query string.
     *
     * Query parameters listed in config('docsystem.ignored_query_params') are
     * stripped before comparison, so e.g. ?_token=… never creates a new record.
     * Remaining parameters are sorted alphabetically so that ?a=1&b=2 and
     * ?b=2&a=1 resolve to the same page.
     */
    public function findOrCreateByPath(string $urlPath, string $rawQueryString = ''): DocPage
    {
        $queryString = $this->normalizeQueryString($rawQueryString);

        $title = $queryString !== '' ? "{$urlPath}?{$queryString}" : $urlPath;

        return DocPage::firstOrCreate(
            ['url_path' => $urlPath, 'query_string' => $queryString],
            ['title' => $title]
        );
    }

    /**
     * Strip ignored params, sort the rest, and rebuild the query string.
     */
    private function normalizeQueryString(string $raw): string
    {
        if ($raw === '') {
            return '';
        }

        parse_str($raw, $params);

        $ignored = config('docsystem.ignored_query_params', []);
        foreach ($ignored as $key) {
            unset($params[$key]);
        }

        if (empty($params)) {
            return '';
        }

        ksort($params);

        return http_build_query($params);
    }
}
