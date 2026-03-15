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

namespace Qredex\Internal;

use Qredex\Config\RetryPolicy;

final class Retry
{
    public static function shouldRetryStatus(int $status): bool
    {
        return $status === 429 || $status >= 500;
    }

    public static function delayMs(RetryPolicy $policy, int $attempt, ?int $retryAfterSeconds = null): int
    {
        if ($retryAfterSeconds !== null && $retryAfterSeconds > 0) {
            return $retryAfterSeconds * 1000;
        }

        $delay = $policy->baseDelayMs * (2 ** max(0, $attempt - 1));
        $delay = min($policy->maxDelayMs, $delay);

        if (!$policy->useJitter || $delay === 0) {
            return $delay;
        }

        return (int) round($delay * (random_int(80, 120) / 100));
    }

    public static function sleep(int $delayMs): void
    {
        if ($delayMs <= 0) {
            return;
        }

        usleep($delayMs * 1000);
    }
}
