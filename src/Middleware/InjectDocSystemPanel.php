<?php

declare(strict_types=1);

namespace Devtools\DocSystem\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Symfony\Component\HttpFoundation\Response;

class InjectDocSystemPanel
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (app()->environment('production')) {
            return $response;
        }

        if (! Auth::check()) {
            return $response;
        }

        if (! $response instanceof \Illuminate\Http\Response) {
            return $response;
        }

        $contentType = $response->headers->get('Content-Type', '');
        if (! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $content = $response->getContent();

        if (! str_contains($content, '</body>')) {
            return $response;
        }

        // Skip if component was manually included in the layout
        if (str_contains($content, 'docsystem-panel')) {
            return $response;
        }

        try {
            $panel = Blade::render("@livewire('docsystem-panel')");
            $response->setContent(
                str_replace('</body>', $panel . PHP_EOL . '</body>', $content)
            );
        } catch (\Throwable) {
            // Fail silently — never break the host application
        }

        return $response;
    }
}
