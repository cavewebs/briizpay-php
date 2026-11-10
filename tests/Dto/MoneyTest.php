<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Dto;

use BriizPay\Sdk\Dto\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testNormalisesCurrencyToUppercase(): void
    {
        $money = new Money(1485, 'gbp');

        self::assertSame(1485, $money->amountMinor);
        self::assertSame('GBP', $money->currency);
        self::assertSame(['amountMinor' => 1485, 'currency' => 'GBP'], $money->toArray());
    }

    public function testFromArrayReadsMinorUnits(): void
    {
        $money = Money::fromArray(['amountMinor' => 1995, 'currency' => 'EUR']);

        self::assertSame(1995, $money->amountMinor);
        self::assertSame('EUR', $money->currency);
    }

    public function testRejectsNonIsoCurrency(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Currency must be a 3-letter ISO 4217 code.');

        new Money(1, 'GB');
    }

    public function testAllowsZeroAndNegativeMinorUnits(): void
    {
        self::assertSame(0, (new Money(0, 'GBP'))->amountMinor);
        self::assertSame(-15, (new Money(-15, 'GBP'))->amountMinor);
    }
}
