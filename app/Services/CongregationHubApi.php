<?php

namespace App\Services;

use App\Support\ChurchContext;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CongregationHubApi
{
    protected string $baseUrl;

    protected int $cacheTtl;

    protected ChurchContext $church;

    public function __construct(ChurchContext $church)
    {
        $this->baseUrl = rtrim(config('website.api_url'), '/');
        $this->cacheTtl = config('website.cache_ttl');
        $this->church = $church;
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
        // Increment version — all old cache keys become stale and expire via TTL
        Cache::increment($this->church->cachePrefix().':version');
    }

    protected function get(string $path, array $query = []): array
    {
        $http = Http::timeout(10);

        if ($this->church->token) {
            $http = $http->withToken($this->church->token);
        } else {
            $http = $http->withHeaders(['X-Church-Domain' => $this->church->domain]);
        }

        $response = $http->get($this->baseUrl.$path, $query);

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

        // Prefix cache key with church identifier and version for scoped invalidation
        $prefix = $this->church->cachePrefix();
        $version = Cache::get($prefix.':version', 1);
        $versionedKey = $prefix.':'.$key.':v'.$version;

        return Cache::remember($versionedKey, $this->cacheTtl, $callback);
    }
}
