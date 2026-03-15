<?php

declare(strict_types=1);

namespace Qredex\Resource;

use Qredex\Internal\HttpClient;
use Qredex\Internal\Validator;
use Qredex\Model\OrderAttribution;
use Qredex\Model\Page;

final readonly class OrdersClient
{
    public function __construct(private HttpClient $http)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function recordPaidOrder(array $payload): OrderAttribution
    {
        Validator::recordPaidOrder($payload);

        return OrderAttribution::fromArray(
            $this->http->json('POST', '/api/v1/integrations/orders/paid', body: $payload),
        );
    }

    /**
     * @param array<string, mixed> $filters
     * @return Page<OrderAttribution>
     */
    public function list(array $filters = []): Page
    {
        Validator::listOrders($filters);

        return Page::fromArray(
            $this->http->json('GET', '/api/v1/integrations/orders', query: $filters),
            static fn (array $item): OrderAttribution => OrderAttribution::fromArray($item),
        );
    }

    public function getDetails(string $orderAttributionId): OrderAttribution
    {
        Validator::uuid($orderAttributionId, 'orderAttributionId');

        return OrderAttribution::fromArray(
            $this->http->json('GET', "/api/v1/integrations/orders/{$orderAttributionId}/details"),
        );
    }
}
