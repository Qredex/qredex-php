<?php

/**
 *    ▄▄▄▄
 *  ▄█▀▀███▄▄              █▄
 *  ██    ██ ▄             ██
 *  ██    ██ ████▄▄█▀█▄ ▄████ ▄█▀█▄▀██ ██▀
 *  ██  ▄ ██ ██   ██▄█▀ ██ ██ ██▄█▀  ███
 *   ▀█████▄▄█▀  ▄▀█▄▄▄▄█▀███▄▀█▄▄▄▄██ ██▄
 *        ▀█
 *
 *  Copyright (C) 2026 — 2026, Qredex, LTD. All Rights Reserved.
 *
 *  DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 *  Licensed under the Apache License, Version 2.0. See LICENSE for the full license text.
 *  You may not use this file except in compliance with that License.
 *  Unless required by applicable law or agreed to in writing, software distributed under the
 *  License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 *  either express or implied. See the License for the specific language governing permissions
 *  and limitations under the License.
 *
 *  If you need additional information or have any questions, please email: copyright@qredex.com
 */

declare(strict_types=1);

namespace Qredex\Resource;

use Qredex\Error\ApiValidationError;
use Qredex\Error\AuthenticationError;
use Qredex\Error\AuthorizationError;
use Qredex\Error\ConflictError;
use Qredex\Error\NetworkError;
use Qredex\Error\NotFoundError;
use Qredex\Error\RequestValidationError;
use Qredex\Error\ResponseDecodingError;
use Qredex\Internal\HttpClient;
use Qredex\Internal\Validator;
use Qredex\Model\OrderAttribution;
use Qredex\Model\Page;
use Qredex\Request\ListOrdersFilter;
use Qredex\Request\RecordPaidOrderRequest;

final readonly class OrdersClient
{
    public function __construct(private HttpClient $http)
    {
    }

    /**
     * @param array<string, mixed>|RecordPaidOrderRequest $payload
     * Array payloads are compatibility-only. Prefer {@see RecordPaidOrderRequest} for new integrations.
     *
     * @throws RequestValidationError
     * @throws AuthenticationError
     * @throws AuthorizationError
     * @throws ApiValidationError
     * @throws ConflictError
     * @throws NetworkError
     * @throws ResponseDecodingError
     */
    public function recordPaidOrder(array|RecordPaidOrderRequest $payload): OrderAttribution
    {
        if ($payload instanceof RecordPaidOrderRequest) {
            $payload = $payload->toArray();
        } else {
            Validator::recordPaidOrder($payload);
        }

        return OrderAttribution::fromArray(
            $this->http->json('POST', '/api/v1/integrations/orders/paid', body: $payload),
        );
    }

    /**
     * @param array<string, mixed>|ListOrdersFilter $filters
     * @return Page<OrderAttribution>
     * Array filters are compatibility-only. Prefer {@see ListOrdersFilter} for new integrations.
     *
     * @throws RequestValidationError
     * @throws AuthenticationError
     * @throws AuthorizationError
     * @throws NetworkError
     * @throws ResponseDecodingError
     */
    public function list(array|ListOrdersFilter $filters = []): Page
    {
        if ($filters instanceof ListOrdersFilter) {
            $filters = $filters->toArray();
        } else {
            Validator::listOrders($filters);
        }

        return Page::fromArray(
            $this->http->json('GET', '/api/v1/integrations/orders', query: $filters),
            static fn (array $item): OrderAttribution => OrderAttribution::fromArray($item),
        );
    }

    /**
     * @throws RequestValidationError
     * @throws AuthenticationError
     * @throws AuthorizationError
     * @throws NotFoundError
     * @throws NetworkError
     * @throws ResponseDecodingError
     */
    public function getDetails(string $orderAttributionId): OrderAttribution
    {
        Validator::uuid($orderAttributionId, 'orderAttributionId');

        return OrderAttribution::fromArray(
            $this->http->json('GET', "/api/v1/integrations/orders/{$orderAttributionId}/details"),
        );
    }
}
