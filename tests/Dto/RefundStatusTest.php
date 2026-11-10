<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Dto;

use BriizPay\Sdk\Dto\RefundStatus;
use PHPUnit\Framework\TestCase;

final class RefundStatusTest extends TestCase
{
    public function testFromApiAcceptsPluginStatuses(): void
    {
        self::assertSame(RefundStatus::Pending, RefundStatus::fromApi('PENDING'));
        self::assertSame(RefundStatus::Completed, RefundStatus::fromApi('COMPLETED'));
        self::assertSame(RefundStatus::Failed, RefundStatus::fromApi('FAILED'));
        self::assertSame(RefundStatus::Held, RefundStatus::fromApi('HELD'));
    }

    public function testUnknownStatusIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        RefundStatus::fromApi('SENT');
    }
}
