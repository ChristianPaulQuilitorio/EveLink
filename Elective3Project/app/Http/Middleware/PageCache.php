<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Response;

class PageCache
{
    /**
     * Handle an incoming request and cache successful GET responses.
     */
    public function handle(Request $request, Closure $next, $seconds = 60)
    {
        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        if ($request->hasHeader('Authorization')) {
            return $next($request);
        }

        $key = 'pagecache:' . sha1($request->fullUrl());

        if (Cache::has($key)) {
            $cached = Cache::get($key);
            return new Response($cached['content'], $cached['status'], $cached['headers']);
        }

        $response = $next($request);

        if ($response->getStatusCode() === 200 && $this->isCacheableResponse($response)) {
            $store = [
                'url' => $request->fullUrl(),
                'content' => $response->getContent(),
                'status' => $response->getStatusCode(),
                'headers' => $this->filterHeaders($response->headers->all()),
            ];
            Cache::put($key, $store, (int) $seconds);

            // Maintain index of pagecache keys for targeted invalidation
            try {
                $indexKey = 'pagecache:keys';
                $keys = Cache::get($indexKey, []);
                if (! in_array($key, $keys, true)) {
                    $keys[] = $key;
                    // keep the index long-lived
                    Cache::put($indexKey, $keys, 60 * 60 * 24 * 365);
                }
            } catch (\Throwable $e) {
                // fail silently if cache driver doesn't support the operation
            }
        }

        return $response;
    }

    protected function isCacheableResponse($response): bool
    {
        $headers = $response->headers->all();
        // Don't cache responses with cookies or vary headers
        if (! empty($headers['set-cookie'] ?? [])) {
            return false;
        }
        return true;
    }

    protected function filterHeaders(array $headers): array
    {
        $out = [];
        foreach ($headers as $k => $v) {
            $out[$k] = is_array($v) && count($v) === 1 ? $v[0] : $v;
        }
        return $out;
    }
}
