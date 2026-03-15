<?php

declare(strict_types=1);

namespace Qredex\Internal;

use Qredex\Error\ApiError;
use Qredex\Error\AuthenticationError;
use Qredex\Error\AuthorizationError;
use Qredex\Error\ConflictError;
use Qredex\Error\QredexError;
use Qredex\Error\RateLimitError;
use Qredex\Error\ValidationError;
use Qredex\Http\TransportResponse;

final class ErrorFactory
{
    public static function fromResponse(TransportResponse $response): QredexError
    {
        $decoded = json_decode($response->body, true);
        $payload = is_array($decoded) ? $decoded : null;
        $message = is_array($payload) && is_string($payload['message'] ?? null)
            ? $payload['message']
            : "Qredex API request failed with status {$response->status}.";
        $errorCode = is_array($payload) && is_string($payload['error_code'] ?? null)
            ? $payload['error_code']
            : null;
        $requestId = $response->header('x-request-id');
        $traceId = $response->header('x-trace-id');
        $retryAfter = $response->header('retry-after');
        $retryAfterSeconds = is_string($retryAfter) && ctype_digit($retryAfter) ? (int) $retryAfter : null;

        return match ($response->status) {
            400, 422 => new ValidationError(
                message: $message,
                status: $response->status,
                errorCode: $errorCode,
                requestId: $requestId,
                traceId: $traceId,
                responseBody: $payload,
                responseText: $response->body,
                retryAfterSeconds: $retryAfterSeconds,
            ),
            401 => new AuthenticationError(
                message: $message,
                status: $response->status,
                errorCode: $errorCode,
                requestId: $requestId,
                traceId: $traceId,
                responseBody: $payload,
                responseText: $response->body,
                retryAfterSeconds: $retryAfterSeconds,
            ),
            403 => new AuthorizationError(
                message: $message,
                status: $response->status,
                errorCode: $errorCode,
                requestId: $requestId,
                traceId: $traceId,
                responseBody: $payload,
                responseText: $response->body,
                retryAfterSeconds: $retryAfterSeconds,
            ),
            409 => new ConflictError(
                message: $message,
                status: $response->status,
                errorCode: $errorCode,
                requestId: $requestId,
                traceId: $traceId,
                responseBody: $payload,
                responseText: $response->body,
                retryAfterSeconds: $retryAfterSeconds,
            ),
            429 => new RateLimitError(
                message: $message,
                status: $response->status,
                errorCode: $errorCode,
                requestId: $requestId,
                traceId: $traceId,
                responseBody: $payload,
                responseText: $response->body,
                retryAfterSeconds: $retryAfterSeconds,
            ),
            default => new ApiError(
                message: $message,
                status: $response->status,
                errorCode: $errorCode,
                requestId: $requestId,
                traceId: $traceId,
                responseBody: $payload,
                responseText: $response->body,
                retryAfterSeconds: $retryAfterSeconds,
            ),
        };
    }
}
