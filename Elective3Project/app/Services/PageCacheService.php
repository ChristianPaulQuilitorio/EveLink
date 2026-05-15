<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class PageCacheService
{
    protected string $indexKey = 'pagecache:keys';

    public function purgeUrl(string $url): void
    {
        $key = 'pagecache:' . sha1($url);
        Cache::forget($key);
        $this->removeKeyFromIndex($key);
    }

    public function purgePattern(string $pattern): void
    {
        $keys = Cache::get($this->indexKey, []);
        if (empty($keys)) {
            return;
        }

        $kept = [];
        foreach ($keys as $key) {
            $entry = Cache::get($key);
            if (! $entry || ! isset($entry['url'])) {
                continue;
            }

            if (str_contains($entry['url'], $pattern)) {
                Cache::forget($key);
                continue;
            }

            $kept[] = $key;
        }

        Cache::put($this->indexKey, array_values($kept), 60 * 60 * 24 * 365);
    }

    public function purgeAll(): void
    {
        $keys = Cache::get($this->indexKey, []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        Cache::forget($this->indexKey);
    }

    protected function removeKeyFromIndex(string $key): void
    {
        $keys = Cache::get($this->indexKey, []);
        $filtered = array_values(array_filter($keys, fn($k) => $k !== $key));
        Cache::put($this->indexKey, $filtered, 60 * 60 * 24 * 365);
    }
}
