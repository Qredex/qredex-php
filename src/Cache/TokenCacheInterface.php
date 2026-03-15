<?php

declare(strict_types=1);

namespace Qredex\Cache;

interface TokenCacheInterface
{
    public function get(): ?CachedToken;

    public function set(CachedToken $token): void;

    public function clear(): void;
}
