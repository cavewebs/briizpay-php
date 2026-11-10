<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Dto;

use BriizPay\Sdk\Dto\LineItem;
use PHPUnit\Framework\TestCase;

final class LineItemTest extends TestCase
{
    public function testRoundTripMatchesPluginShape(): void
    {
        $item = LineItem::fromArray([
            'name' => 'T-shirt',
            'quantity' => 1,
            'unitPriceMinor' => 1500,
            'taxRateBps' => 2000,
            'taxInclusive' => true,
            'sku' => 'TEE-1',
        ]);

        self::assertSame('T-shirt', $item->name);
        self::assertSame(1, $item->quantity);
        self::assertSame(1500, $item->unitPriceMinor);
        self::assertSame(2000, $item->taxRateBps);
        self::assertTrue($item->taxInclusive);
        self::assertSame('TEE-1', $item->sku);
        self::assertNull($item->description);
        self::assertSame(
            [
                'name' => 'T-shirt',
                'quantity' => 1,
                'unitPriceMinor' => 1500,
                'taxRateBps' => 2000,
                'taxInclusive' => true,
                'sku' => 'TEE-1',
            ],
            $item->toArray(),
        );
    }

    public function testDiscountLineIsNegative(): void
    {
        $item = new LineItem('Pay by bank discount', 1, -15);

        self::assertSame(-15, $item->unitPriceMinor);
        self::assertSame(0, $item->taxRateBps);
        self::assertTrue($item->taxInclusive);
    }

    public function testFractionalQuantityStaysFloat(): void
    {
        $item = new LineItem('Coffee', 1.5, 250);
        $payload = $item->toArray();

        self::assertSame(1.5, $payload['quantity']);
    }

    public function testWholeFloatQuantityBecomesInt(): void
    {
        $item = new LineItem('Coffee', 2.0, 250);

        self::assertSame(2, $item->toArray()['quantity']);
    }

    public function testDefaultsTaxInclusiveWhenMissing(): void
    {
        $item = LineItem::fromArray([
            'name' => 'Mug',
            'quantity' => 2,
            'unitPriceMinor' => 400,
        ]);

        self::assertTrue($item->taxInclusive);
        self::assertSame(0, $item->taxRateBps);
    }

    public function testRejectsEmptyName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new LineItem('  ', 1, 100);
    }

    public function testRejectsZeroQuantity(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new LineItem('Mug', 0, 100);
    }

    public function testRejectsNonFiniteQuantity(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new LineItem('Mug', INF, 100);
    }

    public function testRejectsTaxRateAbove100Percent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new LineItem('Mug', 1, 100, 10001);
    }

    public function testBlankSkuBecomesNull(): void
    {
        $item = new LineItem('Mug', 1, 100, sku: '  ');

        self::assertNull($item->sku);
        self::assertArrayNotHasKey('sku', $item->toArray());
    }
}
