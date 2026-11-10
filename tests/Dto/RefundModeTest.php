<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Dto;

use BriizPay\Sdk\Dto\RefundMode;
use PHPUnit\Framework\TestCase;

final class RefundModeTest extends TestCase
{
    public function testValuesMatchApi(): void
    {
        self::assertSame('auto', RefundMode::Auto->value);
        self::assertSame('manual', RefundMode::Manual->value);
    }
}
