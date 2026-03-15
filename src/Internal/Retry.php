<?php

declare(strict_types=1);

namespace Qredex\Internal;

use Qredex\Config\RetryPolicy;

final class Retry
{
    public static function shouldRetryStatus(int $status): bool
    {
        return $status === 429 || $status >= 500;
    }

    public static function delayMs(RetryPolicy $policy, int $attempt): int
    {
        $delay = $policy->baseDelayMs * (2 ** max(0, $attempt - 1));

        return min($policy->maxDelayMs, $delay);
    }

    public static function sleep(int $delayMs): void
    {
        if ($delayMs <= 0) {
            return;
        }

        usleep($delayMs * 1000);
    }
}
