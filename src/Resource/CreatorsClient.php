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
use Qredex\Model\Creator;
use Qredex\Model\Page;
use Qredex\Request\CreateCreatorRequest;
use Qredex\Request\ListCreatorsFilter;

final readonly class CreatorsClient
{
    public function __construct(private HttpClient $http)
    {
    }

    /**
     * @param array<string, mixed>|CreateCreatorRequest $payload
     * Array payloads are compatibility-only. Prefer {@see CreateCreatorRequest} for new integrations.
     *
     * @throws RequestValidationError
     * @throws AuthenticationError
     * @throws AuthorizationError
     * @throws ApiValidationError
     * @throws ConflictError
     * @throws NetworkError
     * @throws ResponseDecodingError
     */
    public function create(array|CreateCreatorRequest $payload): Creator
    {
        if ($payload instanceof CreateCreatorRequest) {
            $payload = $payload->toArray();
        } else {
            Validator::createCreator($payload);
        }

        return Creator::fromArray(
            $this->http->json('POST', '/api/v1/integrations/creators', body: $payload),
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
    public function get(string $creatorId): Creator
    {
        Validator::uuid($creatorId, 'creatorId');

        return Creator::fromArray(
            $this->http->json('GET', "/api/v1/integrations/creators/{$creatorId}"),
        );
    }

    /**
     * @param array<string, mixed>|ListCreatorsFilter $filters
     * @return Page<Creator>
     * Array filters are compatibility-only. Prefer {@see ListCreatorsFilter} for new integrations.
     *
     * @throws RequestValidationError
     * @throws AuthenticationError
     * @throws AuthorizationError
     * @throws NetworkError
     * @throws ResponseDecodingError
     */
    public function list(array|ListCreatorsFilter $filters = []): Page
    {
        if ($filters instanceof ListCreatorsFilter) {
            $filters = $filters->toArray();
        } else {
            Validator::listCreators($filters);
        }

        return Page::fromArray(
            $this->http->json('GET', '/api/v1/integrations/creators', query: $filters),
            static fn (array $item): Creator => Creator::fromArray($item),
        );
    }
}
