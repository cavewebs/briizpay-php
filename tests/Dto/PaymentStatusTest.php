<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Dto;

use BriizPay\Sdk\Dto\PaymentStatus;
use PHPUnit\Framework\TestCase;

final class PaymentStatusTest extends TestCase
{
    public function testFromApiAcceptsPluginStatuses(): void
    {
        self::assertSame(PaymentStatus::Pending, PaymentStatus::fromApi('PENDING'));
        self::assertSame(PaymentStatus::Cancelled, PaymentStatus::fromApi('CANCELLED'));
        self::assertSame(PaymentStatus::Failed, PaymentStatus::fromApi('FAILED'));
        self::assertSame(PaymentStatus::Completed, PaymentStatus::fromApi('COMPLETED'));
        self::assertSame(PaymentStatus::Expired, PaymentStatus::fromApi('EXPIRED'));
    }

    public function testPaidMeansCompleted(): void
    {
        self::assertTrue(PaymentStatus::Completed->isPaid());
        self::assertFalse(PaymentStatus::Pending->isPaid());
    }
}
