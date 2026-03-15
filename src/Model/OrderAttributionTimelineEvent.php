<?php

declare(strict_types=1);

namespace Qredex\Model;

use JsonSerializable;
use Qredex\Internal\ArrayMapper;

final readonly class OrderAttributionTimelineEvent implements JsonSerializable
{
    public function __construct(
        public ?string $eventType,
        public ?string $occurredAt,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            eventType: ArrayMapper::nullableString($payload, 'event_type'),
            occurredAt: ArrayMapper::nullableString($payload, 'occurred_at'),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'event_type' => $this->eventType,
            'occurred_at' => $this->occurredAt,
        ];
    }
}
