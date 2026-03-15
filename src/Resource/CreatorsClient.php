<?php

declare(strict_types=1);

namespace Qredex\Resource;

use Qredex\Internal\HttpClient;
use Qredex\Internal\Validator;
use Qredex\Model\Creator;
use Qredex\Model\Page;

final readonly class CreatorsClient
{
    public function __construct(private HttpClient $http)
    {
    }

    /**
     * @param array<string, mixed> $payload
     * @throws \Exception
     */
    public function create(array $payload): Creator
    {
        Validator::createCreator($payload);

        return Creator::fromArray(
            $this->http->json('POST', '/api/v1/integrations/creators', body: $payload),
        );
    }

    public function get(string $creatorId): Creator
    {
        Validator::uuid($creatorId, 'creatorId');

        return Creator::fromArray(
            $this->http->json('GET', "/api/v1/integrations/creators/{$creatorId}"),
        );
    }

    /**
     * @param array<string, mixed> $filters
     * @return Page<Creator>
     */
    public function list(array $filters = []): Page
    {
        Validator::listCreators($filters);

        return Page::fromArray(
            $this->http->json('GET', '/api/v1/integrations/creators', query: $filters),
            static fn (array $item): Creator => Creator::fromArray($item),
        );
    }
}
