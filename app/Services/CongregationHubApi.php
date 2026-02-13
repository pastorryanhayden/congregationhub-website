<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CongregationHubApi
{
    protected string $baseUrl;

    protected string $token;

    protected int $cacheTtl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('website.api_url'), '/');
        $this->token = config('website.api_token');
        $this->cacheTtl = config('website.cache_ttl');
    }

    public function homepage(): array
    {
        return $this->cached('website:homepage', function () {
            return $this->get('/api/website/homepage');
        });
    }

    public function page(string $slug, array $query = []): array
    {
        $cacheKey = "website:page:{$slug}";
        if (! empty($query)) {
            $cacheKey .= ':'.md5(http_build_query($query));
        }

        return $this->cached($cacheKey, function () use ($slug, $query) {
            return $this->get('/api/website/pages/'.$slug, $query);
        });
    }

    public function clearCache(): void
    {
        Cache::flush();
    }

    protected function get(string $path, array $query = []): array
    {
        $response = Http::withToken($this->token)
            ->timeout(10)
            ->get($this->baseUrl.$path, $query);

        if ($response->failed()) {
            abort($response->status(), 'API request failed');
        }

        return $response->json();
    }

    protected function cached(string $key, callable $callback): array
    {
        if ($this->cacheTtl <= 0) {
            return $callback();
        }

        return Cache::remember($key, $this->cacheTtl, $callback);
    }
}
