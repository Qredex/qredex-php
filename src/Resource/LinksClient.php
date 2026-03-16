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

use Qredex\Internal\HttpClient;
use Qredex\Internal\Validator;
use Qredex\Model\Link;
use Qredex\Model\LinkStats;
use Qredex\Model\Page;
use Qredex\Request\CreateLinkRequest;

final readonly class LinksClient
{
    public function __construct(private HttpClient $http)
    {
    }

    /**
     * @param array<string, mixed>|CreateLinkRequest $payload
     */
    public function create(array|CreateLinkRequest $payload): Link
    {
        $payload = $payload instanceof CreateLinkRequest ? $payload->toArray() : $payload;
        Validator::createLink($payload);

        return Link::fromArray(
            $this->http->json('POST', '/api/v1/integrations/links', body: $payload),
        );
    }

    public function get(string $linkId): Link
    {
        Validator::uuid($linkId, 'linkId');

        return Link::fromArray(
            $this->http->json('GET', "/api/v1/integrations/links/{$linkId}"),
        );
    }

    /**
     * @param array<string, mixed> $filters
     * @return Page<Link>
     */
    public function list(array $filters = []): Page
    {
        Validator::listLinks($filters);

        return Page::fromArray(
            $this->http->json('GET', '/api/v1/integrations/links', query: $filters),
            static fn (array $item): Link => Link::fromArray($item),
        );
    }

    public function getStats(string $linkId): LinkStats
    {
        Validator::uuid($linkId, 'linkId');

        return LinkStats::fromArray(
            $this->http->json('GET', "/api/v1/integrations/links/{$linkId}/stats"),
        );
    }
}
