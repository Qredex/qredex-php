<?php

declare(strict_types=1);

namespace Qredex\Model;

use JsonSerializable;
use Qredex\Internal\ArrayMapper;

final readonly class LinkStats implements JsonSerializable
{
    public function __construct(
        public string $linkId,
        public int $clicksCount,
        public int $sessionsCount,
        public int $ordersCount,
        public ?float $revenueTotal,
        public int $tokenInvalidCount,
        public int $tokenMissingCount,
        public ?string $lastClickAt,
        public ?string $lastOrderAt,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            linkId: ArrayMapper::string($payload, 'link_id'),
            clicksCount: ArrayMapper::int($payload, 'clicks_count'),
            sessionsCount: ArrayMapper::int($payload, 'sessions_count'),
            ordersCount: ArrayMapper::int($payload, 'orders_count'),
            revenueTotal: ArrayMapper::nullableFloat($payload, 'revenue_total'),
            tokenInvalidCount: ArrayMapper::int($payload, 'token_invalid_count'),
            tokenMissingCount: ArrayMapper::int($payload, 'token_missing_count'),
            lastClickAt: ArrayMapper::nullableString($payload, 'last_click_at'),
            lastOrderAt: ArrayMapper::nullableString($payload, 'last_order_at'),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'link_id' => $this->linkId,
            'clicks_count' => $this->clicksCount,
            'sessions_count' => $this->sessionsCount,
            'orders_count' => $this->ordersCount,
            'revenue_total' => $this->revenueTotal,
            'token_invalid_count' => $this->tokenInvalidCount,
            'token_missing_count' => $this->tokenMissingCount,
            'last_click_at' => $this->lastClickAt,
            'last_order_at' => $this->lastOrderAt,
        ];
    }
}
