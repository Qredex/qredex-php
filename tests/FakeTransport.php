<?php

declare(strict_types=1);

namespace Qredex\Tests;

use Qredex\Error\NetworkError;
use Qredex\Http\HttpTransportInterface;
use Qredex\Http\TransportRequest;
use Qredex\Http\TransportResponse;

final class FakeTransport implements HttpTransportInterface
{
    /**
     * @var list<TransportResponse|\Throwable>
     */
    private array $queue = [];

    /**
     * @var list<TransportRequest>
     */
    public array $requests = [];

    public function push(TransportResponse|\Throwable $response): void
    {
        $this->queue[] = $response;
    }

    public function send(TransportRequest $request): TransportResponse
    {
        $this->requests[] = $request;
        $response = array_shift($this->queue);

        if ($response instanceof \Throwable) {
            throw new NetworkError($response->getMessage(), previous: $response);
        }

        if (!$response instanceof TransportResponse) {
            throw new NetworkError('Fake transport queue is empty.');
        }

        return $response;
    }
}
