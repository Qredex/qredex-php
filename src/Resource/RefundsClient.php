<?php

declare(strict_types=1);

namespace Qredex\Resource;

use Qredex\Internal\HttpClient;
use Qredex\Internal\Validator;
use Qredex\Model\OrderAttribution;

final readonly class RefundsClient
{
    public function __construct(private HttpClient $http)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function recordRefund(array $payload): OrderAttribution
    {
        Validator::recordRefund($payload);

        return OrderAttribution::fromArray(
            $this->http->json('POST', '/api/v1/integrations/orders/refund', body: $payload),
        );
    }
}
