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

use Qredex\Error\RequestValidationError;

final class Validator
{
    private const ALLOWED_ATTRIBUTION_WINDOWS = [1, 3, 7, 14, 30];
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    private const ISO_8601_UTC_PATTERN = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d{1,9})?Z$/';
    private const CURRENCY_PATTERN = '/^[A-Z]{3}$/';

    /**
     * @param array<string, mixed> $payload
     */
    public static function createCreator(array $payload): void
    {
        self::requireNonEmptyString($payload, 'handle');
        self::optionalNonEmptyString($payload, 'display_name');
        self::optionalNonEmptyString($payload, 'email');
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function listCreators(array $payload): void
    {
        self::optionalNonNegativeInt($payload, 'page');
        self::optionalNonNegativeInt($payload, 'size');
        self::optionalNonEmptyString($payload, 'status');
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function createLink(array $payload): void
    {
        self::requireUuid($payload, 'store_id');
        self::requireUuid($payload, 'creator_id');
        self::requireNonEmptyString($payload, 'link_name');
        self::requireNonEmptyString($payload, 'destination_path');

        if (!str_starts_with((string) $payload['destination_path'], '/')) {
            self::fail("destination_path must start with '/'.");
        }

        self::optionalNonEmptyString($payload, 'note');
        self::optionalIso8601Utc($payload, 'link_expiry_at');
        self::optionalNonEmptyString($payload, 'discount_code');
        self::optionalNonEmptyString($payload, 'status');

        if (
            array_key_exists('attribution_window_days', $payload)
            && $payload['attribution_window_days'] !== null
            && (!is_int($payload['attribution_window_days']) || !in_array($payload['attribution_window_days'], self::ALLOWED_ATTRIBUTION_WINDOWS, true))
        ) {
            self::fail('attribution_window_days must be one of 1, 3, 7, 14, or 30.');
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function listLinks(array $payload): void
    {
        self::optionalNonNegativeInt($payload, 'page');
        self::optionalNonNegativeInt($payload, 'size');
        self::optionalNonEmptyString($payload, 'status');
        self::optionalNonEmptyString($payload, 'destination');

        if (array_key_exists('expired', $payload) && !is_bool($payload['expired'])) {
            self::fail('expired must be a boolean when provided.');
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function issueInfluenceIntentToken(array $payload): void
    {
        self::requireUuid($payload, 'link_id');
        self::optionalNonEmptyString($payload, 'ip_hash');
        self::optionalNonEmptyString($payload, 'user_agent_hash');
        self::optionalNonEmptyString($payload, 'referrer');
        self::optionalNonEmptyString($payload, 'landing_path');
        self::optionalIso8601Utc($payload, 'expires_at');
        self::optionalPositiveInt($payload, 'integrity_version');
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function lockPurchaseIntent(array $payload): void
    {
        self::requireNonEmptyString($payload, 'token');
        self::optionalNonEmptyString($payload, 'source');
        self::optionalPositiveInt($payload, 'integrity_version');
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function listOrders(array $payload): void
    {
        self::optionalNonNegativeInt($payload, 'page');
        self::optionalNonNegativeInt($payload, 'size');
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function recordPaidOrder(array $payload): void
    {
        self::requireUuid($payload, 'store_id');
        self::requireNonEmptyString($payload, 'external_order_id');
        self::requireCurrency($payload, 'currency');
        self::optionalNonEmptyString($payload, 'order_number');
        self::optionalIso8601Utc($payload, 'paid_at');
        self::optionalNonNegativeNumber($payload, 'subtotal_price');
        self::optionalNonNegativeNumber($payload, 'discount_total');
        self::optionalNonNegativeNumber($payload, 'total_price');
        self::optionalNonEmptyString($payload, 'customer_email_hash');
        self::optionalNonEmptyString($payload, 'checkout_token');
        self::optionalNonEmptyString($payload, 'purchase_intent_token');
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function recordRefund(array $payload): void
    {
        self::requireUuid($payload, 'store_id');
        self::requireNonEmptyString($payload, 'external_order_id');
        self::requireNonEmptyString($payload, 'external_refund_id');
        self::optionalNonNegativeNumber($payload, 'refund_total');
        self::optionalIso8601Utc($payload, 'refunded_at');
    }

    public static function uuid(string $value, string $field): void
    {
        if (!preg_match(self::UUID_PATTERN, $value)) {
            self::fail("{$field} must be a valid UUID.");
        }
    }

    public static function nonEmptyString(string $value, string $field): void
    {
        if (trim($value) === '') {
            self::fail("{$field} must be a non-empty string.");
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function requireNonEmptyString(array $payload, string $field): void
    {
        $value = $payload[$field] ?? null;

        if (!is_string($value) || trim($value) === '') {
            self::fail("{$field} must be a non-empty string.");
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function optionalNonEmptyString(array $payload, string $field): void
    {
        $value = $payload[$field] ?? null;

        if ($value !== null && (!is_string($value) || trim($value) === '')) {
            self::fail("{$field} must be a non-empty string when provided.");
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function requireUuid(array $payload, string $field): void
    {
        $value = $payload[$field] ?? null;

        if (!is_string($value) || !preg_match(self::UUID_PATTERN, $value)) {
            self::fail("{$field} must be a valid UUID.");
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function optionalIso8601Utc(array $payload, string $field): void
    {
        $value = $payload[$field] ?? null;

        if ($value !== null && (!is_string($value) || !preg_match(self::ISO_8601_UTC_PATTERN, $value))) {
            self::fail("{$field} must be an ISO 8601 UTC timestamp when provided.");
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function optionalPositiveInt(array $payload, string $field): void
    {
        $value = $payload[$field] ?? null;

        if ($value !== null && (!is_int($value) || $value <= 0)) {
            self::fail("{$field} must be a positive integer when provided.");
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function optionalNonNegativeInt(array $payload, string $field): void
    {
        $value = $payload[$field] ?? null;

        if ($value !== null && (!is_int($value) || $value < 0)) {
            self::fail("{$field} must be an integer greater than or equal to 0 when provided.");
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function optionalNonNegativeNumber(array $payload, string $field): void
    {
        $value = $payload[$field] ?? null;

        if ($value !== null && ((!is_int($value) && !is_float($value)) || $value < 0)) {
            self::fail("{$field} must be a finite number greater than or equal to 0 when provided.");
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function requireCurrency(array $payload, string $field): void
    {
        $value = $payload[$field] ?? null;

        if (!is_string($value) || !preg_match(self::CURRENCY_PATTERN, $value)) {
            self::fail("{$field} must be a 3-letter uppercase ISO currency code.");
        }
    }

    private static function fail(string $message): never
    {
        throw new RequestValidationError($message, errorCode: 'sdk_validation_error');
    }
}
