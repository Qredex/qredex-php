<?php

declare(strict_types=1);

namespace Qredex\Internal;

use Psr\Log\LoggerInterface;
use Qredex\Config\RetryPolicy;
use Qredex\Error\QredexError;
use Qredex\Http\HttpTransportInterface;
use Qredex\Http\TransportRequest;
use Qredex\Http\TransportResponse;

final class HttpClient
{
    /**
     * @param array<string, string> $defaultHeaders
     */
    public function __construct(
        private readonly HttpTransportInterface $transport,
        private readonly TokenProvider $tokenProvider,
        private readonly int $timeoutMs,
        private readonly array $defaultHeaders,
        private readonly EventEmitter $events,
        private readonly ?LoggerInterface $logger = null,
        private readonly ?RetryPolicy $readRetry = null,
    ) {
    }

    /**
     * @param array<string, scalar|null> $query
     * @param array<string, mixed>|null $body
     */
    public function json(string $method, string $path, array $query = [], ?array $body = null): array
    {
        $attempts = $this->readRetry !== null && in_array(strtoupper($method), ['GET', 'HEAD'], true)
            ? $this->readRetry->maxAttempts
            : 1;
        $lastError = null;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                $response = $this->send($method, $path, $query, $body);

                return $this->decode($response);
            } catch (QredexError $error) {
                $lastError = $error;

                if ($this->readRetry === null || $attempt >= $attempts || $error->status === null || !Retry::shouldRetryStatus($error->status)) {
                    throw $error;
                }

                $delay = Retry::delayMs($this->readRetry, $attempt);
                $this->events->emit('retry_scheduled', [
                    'attempt' => $attempt,
                    'delay_ms' => $delay,
                    'max_attempts' => $this->readRetry->maxAttempts,
                    'method' => strtoupper($method),
                    'path' => $path,
                    'source' => 'read',
                ]);
                Retry::sleep($delay);
            }
        }

        throw $lastError ?? new QredexError('Qredex request failed unexpectedly.');
    }

    /**
     * @param array<string, scalar|null> $query
     * @param array<string, mixed>|null $body
     */
    private function send(string $method, string $path, array $query, ?array $body): TransportResponse
    {
        $headers = $this->defaultHeaders + [
            'accept' => 'application/json',
            'user-agent' => $this->defaultHeaders['user-agent'] ?? 'qredex-php/0.1.0',
            'authorization' => $this->tokenProvider->authorizationHeader(),
        ];

        if ($body !== null) {
            $headers['content-type'] = 'application/json';
        }

        $this->events->emit('request', [
            'method' => strtoupper($method),
            'path' => $path,
        ]);

        $this->logger?->debug('qredex.request', [
            'method' => strtoupper($method),
            'path' => $path,
        ]);

        $response = $this->transport->send(new TransportRequest(
            method: strtoupper($method),
            path: $path,
            headers: $headers,
            query: $query,
            body: $body,
            bodyType: $body === null ? TransportRequest::BODY_NONE : TransportRequest::BODY_JSON,
            timeoutMs: $this->timeoutMs,
        ));

        if ($response->status < 200 || $response->status >= 300) {
            throw ErrorFactory::fromResponse($response);
        }

        $this->events->emit('response', [
            'method' => strtoupper($method),
            'path' => $path,
            'status' => $response->status,
            'request_id' => $response->header('x-request-id'),
            'trace_id' => $response->header('x-trace-id'),
        ]);

        $this->logger?->info('qredex.response', [
            'method' => strtoupper($method),
            'path' => $path,
            'status' => $response->status,
            'request_id' => $response->header('x-request-id'),
            'trace_id' => $response->header('x-trace-id'),
        ]);

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(TransportResponse $response): array
    {
        if ($response->body === '') {
            return [];
        }

        $decoded = json_decode($response->body, true);

        if (!is_array($decoded)) {
            throw new QredexError(
                'Qredex returned a non-JSON response.',
                status: $response->status,
                requestId: $response->header('x-request-id'),
                traceId: $response->header('x-trace-id'),
                responseText: $response->body,
            );
        }

        return $decoded;
    }
}
