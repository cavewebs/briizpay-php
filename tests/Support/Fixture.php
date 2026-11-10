<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Support;

final class Fixture
{
    /**
     * @return array<mixed>
     */
    public static function json(string $name): array
    {
        $path = dirname(__DIR__) . '/Fixtures/' . $name;
        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new \RuntimeException('Missing fixture: ' . $name);
        }

        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Fixture is not a JSON object: ' . $name);
        }

        return $decoded;
    }

    public static function raw(string $name): string
    {
        $path = dirname(__DIR__) . '/Fixtures/' . $name;
        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new \RuntimeException('Missing fixture: ' . $name);
        }

        return $raw;
    }
}
