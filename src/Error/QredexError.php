<?php

declare(strict_types=1);

namespace Qredex\Error;

use RuntimeException;
use Throwable;

class QredexError extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $status = null,
        public readonly ?string $errorCode = null,
        public readonly ?string $requestId = null,
        public readonly ?string $traceId = null,
        public readonly mixed $responseBody = null,
        public readonly ?string $responseText = null,
        public readonly ?int $retryAfterSeconds = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
