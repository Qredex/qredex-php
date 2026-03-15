<?php

declare(strict_types=1);

namespace Qredex\Model;

use JsonSerializable;
use Qredex\Internal\ArrayMapper;

final readonly class OAuthToken implements JsonSerializable
{
    public function __construct(
        public string $accessToken,
        public string $tokenType,
        public int $expiresIn,
        public ?string $scope = null,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            accessToken: ArrayMapper::string($payload, 'access_token'),
            tokenType: ArrayMapper::string($payload, 'token_type'),
            expiresIn: ArrayMapper::int($payload, 'expires_in'),
            scope: ArrayMapper::nullableString($payload, 'scope'),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'access_token' => $this->accessToken,
            'token_type' => $this->tokenType,
            'expires_in' => $this->expiresIn,
            'scope' => $this->scope,
        ];
    }
}
