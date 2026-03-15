<?php

declare(strict_types=1);

namespace Qredex\Cache;

final class MemoryTokenCache implements TokenCacheInterface
{
    private ?CachedToken $token = null;

    public function get(): ?CachedToken
    {
        return $this->token;
    }

    public function set(CachedToken $token): void
    {
        $this->token = $token;
    }

    public function clear(): void
    {
        $this->token = null;
    }
}
