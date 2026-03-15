<?php

declare(strict_types=1);

namespace Qredex\Internal;

use Closure;

final readonly class EventEmitter
{
    public function __construct(private ?Closure $listener)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function emit(string $type, array $payload = []): void
    {
        if ($this->listener === null) {
            return;
        }

        ($this->listener)(['type' => $type] + $payload);
    }
}
