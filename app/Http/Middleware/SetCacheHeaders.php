<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies public caching headers to the session-less "static" routes.
 * Only active in production; other environments keep Laravel's defaults.
 */
class SetCacheHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldCacheResponse($request, $response)) {
            return $response;
        }

        $response->headers->set('Cache-Control', 'public, max-age=14400');

        // Allow clients to revalidate with a conditional request
        if ($content = $response->getContent()) {
            $response->setEtag(md5($content));
            $response->isNotModified($request);
        }

        return $response;
    }

    public function shouldCacheResponse(Request $request, Response $response): bool
    {
        return app()->isProduction()
            && $request->isMethod('GET')
            && $response->isSuccessful();
    }
}
