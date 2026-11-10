<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Internal;

/**
 * Typed reads from decoded JSON objects.
 *
 * @internal
 */
final class Map
{
    /**
     * @param array<mixed> $data
     */
    public static function string(array $data, string $key): string
    {
        $value = $data[$key] ?? null;
        if (!is_string($value)) {
            throw new \InvalidArgumentException(sprintf('Expected string "%s".', $key));
        }

        return $value;
    }

    /**
     * @param array<mixed> $data
     */
    public static function optionalString(array $data, string $key): ?string
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }

        if (!is_string($data[$key])) {
            throw new \InvalidArgumentException(sprintf('Expected string or null "%s".', $key));
        }

        return $data[$key];
    }

    /**
     * @param array<mixed> $data
     */
    public static function int(array $data, string $key): int
    {
        $value = self::optionalInt($data, $key);
        if ($value === null) {
            throw new \InvalidArgumentException(sprintf('Expected integer "%s".', $key));
        }

        return $value;
    }

    /**
     * @param array<mixed> $data
     */
    public static function optionalInt(array $data, string $key): ?int
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }

        $value = $data[$key];
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value) && floor($value) === $value) {
            return (int) $value;
        }

        if (is_string($value) && is_numeric($value) && (string) (int) $value === $value) {
            return (int) $value;
        }

        throw new \InvalidArgumentException(sprintf('Expected integer or null "%s".', $key));
    }

    /**
     * @param array<mixed> $data
     */
    public static function bool(array $data, string $key, bool $default = false): bool
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return $default;
        }

        if (!is_bool($data[$key])) {
            throw new \InvalidArgumentException(sprintf('Expected boolean "%s".', $key));
        }

        return $data[$key];
    }

    /**
     * @param array<mixed> $data
     *
     * @return int|float
     */
    public static function number(array $data, string $key): int|float
    {
        $value = $data[$key] ?? null;
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return $value;
        }

        throw new \InvalidArgumentException(sprintf('Expected number "%s".', $key));
    }

    /**
     * @param array<mixed> $data
     *
     * @return list<array<mixed>>
     */
    public static function listOfMaps(array $data, string $key): array
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return [];
        }

        if (!is_array($data[$key])) {
            throw new \InvalidArgumentException(sprintf('Expected list "%s".', $key));
        }

        $out = [];
        foreach ($data[$key] as $item) {
            if (!is_array($item)) {
                throw new \InvalidArgumentException(sprintf('Expected objects in "%s".', $key));
            }

            $out[] = $item;
        }

        return $out;
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    public static function map(array $data, string $key): array
    {
        $value = $data[$key] ?? null;
        if (!is_array($value)) {
            throw new \InvalidArgumentException(sprintf('Expected object "%s".', $key));
        }

        return $value;
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    public static function optionalMap(array $data, string $key): array
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return [];
        }

        if (!is_array($data[$key])) {
            throw new \InvalidArgumentException(sprintf('Expected object or null "%s".', $key));
        }

        return $data[$key];
    }
}
