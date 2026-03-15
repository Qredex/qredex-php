<?php

declare(strict_types=1);

namespace Qredex\Cache;

final readonly class CachedToken
{
    public function __construct(
        public string $accessToken,
        public string $tokenType,
        public int $expiresAtUnix,
        public ?string $scope = null,
    ) {
    }
}
