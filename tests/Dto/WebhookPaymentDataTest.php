<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Dto;

use BriizPay\Sdk\Dto\WebhookPaymentData;
use PHPUnit\Framework\TestCase;

final class WebhookPaymentDataTest extends TestCase
{
    public function testEmptyDataIsAllowed(): void
    {
        $data = WebhookPaymentData::fromArray([]);

        self::assertNull($data->paymentRequestId);
        self::assertNull($data->externalReference);
        self::assertNull($data->amountMinor);
        self::assertNull($data->money());
    }
}
