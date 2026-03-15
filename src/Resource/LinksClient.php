<?php

declare(strict_types=1);

namespace Qredex\Resource;

use Qredex\Internal\HttpClient;
use Qredex\Internal\Validator;
use Qredex\Model\Link;
use Qredex\Model\LinkStats;
use Qredex\Model\Page;

final readonly class LinksClient
{
    public function __construct(private HttpClient $http)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): Link
    {
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
