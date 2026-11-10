<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Dto;

use BriizPay\Sdk\Dto\Discount;
use PHPUnit\Framework\TestCase;

final class DiscountTest extends TestCase
{
    public function testBecomesANegativeLineItem(): void
    {
        $discount = new Discount('Pay by bank discount', 15);
        $line = $discount->toLineItem();

        self::assertSame('Pay by bank discount', $line->name);
        self::assertSame(1, $line->quantity);
        self::assertSame(-15, $line->unitPriceMinor);
        self::assertSame(0, $line->taxRateBps);
        self::assertTrue($line->taxInclusive);
    }

    public function testRejectsEmptyName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Discount(' ', 10);
    }

    public function testRejectsNonPositiveMagnitude(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Discount('Saving', 0);
    }
}
