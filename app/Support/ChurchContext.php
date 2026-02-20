<?php

namespace App\Support;

class ChurchContext
{
    public function __construct(
        public readonly ?string $token = null,
        public readonly ?string $domain = null,
    ) {}

    /**
     * Stable string for cache key prefixes.
     */
    public function cachePrefix(): string
    {
        return $this->domain
            ? 'church:'.str_replace('.', '_', $this->domain)
            : 'church:'.substr(md5($this->token ?? 'default'), 0, 12);
    }

    public function isMultiTenant(): bool
    {
        return $this->token === null && $this->domain !== null;
    }
}
