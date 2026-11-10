<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Dto;

use BriizPay\Sdk\Dto\WebhookEventType;
use PHPUnit\Framework\TestCase;

final class WebhookEventTypeTest extends TestCase
{
    public function testPaidEventValue(): void
    {
        self::assertSame('payment_request.paid', WebhookEventType::PaymentRequestPaid->value);
    }
}
