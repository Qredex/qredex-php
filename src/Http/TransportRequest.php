<?php

declare(strict_types=1);

namespace Qredex\Http;

final readonly class TransportRequest
{
    public const BODY_NONE = 'none';
    public const BODY_JSON = 'json';
    public const BODY_FORM = 'form';

    /**
     * @param array<string, string> $headers
     * @param array<string, scalar|null> $query
     * @param array<string, mixed>|string|null $body
     */
    public function __construct(
        public string $method,
        public string $path,
        public array $headers = [],
        public array $query = [],
        public array|string|null $body = null,
        public string $bodyType = self::BODY_NONE,
        public int $timeoutMs = 10_000,
    ) {
    }
}
