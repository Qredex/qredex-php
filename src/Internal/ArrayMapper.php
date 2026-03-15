<?php

declare(strict_types=1);

namespace Qredex\Internal;

use Qredex\Error\ValidationError;

final class ArrayMapper
{
    /**
     * @param array<string, mixed> $payload
     */
    public static function string(array $payload, string $key): string
    {
        $value = $payload[$key] ?? null;

        if (!is_string($value)) {
            throw new ValidationError("Expected '{$key}' to be a string in the Qredex response.", errorCode: 'sdk_response_error', responseBody: $payload);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function nullableString(array $payload, string $key): ?string
    {
        $value = $payload[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function int(array $payload, string $key): int
    {
        $value = $payload[$key] ?? null;

        if (!is_int($value)) {
            throw new ValidationError("Expected '{$key}' to be an int in the Qredex response.", errorCode: 'sdk_response_error', responseBody: $payload);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function nullableInt(array $payload, string $key): ?int
    {
        $value = $payload[$key] ?? null;

        return is_int($value) ? $value : null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function nullableFloat(array $payload, string $key): ?float
    {
        $value = $payload[$key] ?? null;

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function bool(array $payload, string $key): bool
    {
        $value = $payload[$key] ?? null;

        if (!is_bool($value)) {
            throw new ValidationError("Expected '{$key}' to be a bool in the Qredex response.", errorCode: 'sdk_response_error', responseBody: $payload);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, string>
     */
    public static function stringMap(array $payload, string $key): array
    {
        $value = $payload[$key] ?? [];

        if (!is_array($value)) {
            return [];
        }

        $mapped = [];

        foreach ($value as $mapKey => $mapValue) {
            if (is_string($mapKey) && is_string($mapValue)) {
                $mapped[$mapKey] = $mapValue;
            }
        }

        return $mapped;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<int, mixed>
     */
    public static function list(array $payload, string $key): array
    {
        $value = $payload[$key] ?? [];

        return is_array($value) ? array_values($value) : [];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>|null
     */
    public static function nullableArray(array $payload, string $key): ?array
    {
        $value = $payload[$key] ?? null;

        return is_array($value) ? $value : null;
    }
}
