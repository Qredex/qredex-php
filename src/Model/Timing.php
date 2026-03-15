<?php

declare(strict_types=1);

namespace Qredex\Model;

use JsonSerializable;
use Qredex\Internal\ArrayMapper;

final readonly class Timing implements JsonSerializable
{
    public function __construct(
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            createdAt: ArrayMapper::string($payload, 'created_at'),
            updatedAt: ArrayMapper::string($payload, 'updated_at'),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
