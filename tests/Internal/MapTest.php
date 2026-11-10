<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Internal;

use BriizPay\Sdk\Internal\Map;
use PHPUnit\Framework\TestCase;

final class MapTest extends TestCase
{
    public function testOptionalIntAcceptsNumericStringAndWholeFloat(): void
    {
        self::assertSame(12, Map::optionalInt(['n' => '12'], 'n'));
        self::assertSame(12, Map::optionalInt(['n' => 12.0], 'n'));
        self::assertNull(Map::optionalInt(['n' => null], 'n'));
    }

    public function testOptionalIntRejectsPartialNumericString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Map::optionalInt(['n' => '12px'], 'n');
    }

    public function testNumberAcceptsIntAndFloat(): void
    {
        self::assertSame(2, Map::number(['q' => 2], 'q'));
        self::assertSame(1.5, Map::number(['q' => 1.5], 'q'));
    }

    public function testListOfMaps(): void
    {
        self::assertSame([], Map::listOfMaps([], 'lineItems'));
        self::assertSame([['name' => 'A']], Map::listOfMaps(['lineItems' => [['name' => 'A']]], 'lineItems'));
    }

    public function testMapHelpers(): void
    {
        self::assertSame(['a' => 1], Map::map(['data' => ['a' => 1]], 'data'));
        self::assertSame([], Map::optionalMap([], 'data'));
    }

    public function testStringRequired(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Map::string(['id' => 1], 'id');
    }
}
