<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Dto;

use BriizPay\Sdk\Dto\WebhookEvent;
use BriizPay\Sdk\Dto\WebhookEventType;
use BriizPay\Sdk\Dto\WebhookPaymentData;
use BriizPay\Sdk\Tests\Support\Fixture;
use PHPUnit\Framework\TestCase;

final class WebhookEventTest extends TestCase
{
    public function testFromPaidFixture(): void
    {
        $event = WebhookEvent::fromArray(Fixture::json('webhook-paid.json'));

        self::assertSame('evt_magento', $event->id);
        self::assertSame(WebhookEventType::PaymentRequestPaid->value, $event->type);
        self::assertTrue($event->isPaymentRequestPaid());
        self::assertSame(WebhookEventType::PaymentRequestPaid, $event->eventType());
        self::assertSame('req_1', $event->data->paymentRequestId);
        self::assertSame('000000042', $event->data->externalReference);
        self::assertSame(1499, $event->data->amountMinor);
        self::assertSame('txn_9f', $event->data->transactionId);
        self::assertNull($event->data->currency);
        $money = $event->data->money();
        self::assertNotNull($money);
        self::assertSame(1499, $money->amountMinor);
        self::assertSame('GBP', $money->currency);
    }

    public function testOptionalCurrencyOnWebhookData(): void
    {
        $event = WebhookEvent::fromArray([
            'id' => 'evt_1',
            'type' => 'payment_request.paid',
            'data' => [
                'paymentRequestId' => 'pr_1',
                'amountMinor' => 100,
                'currency' => 'EUR',
            ],
        ]);

        $money = $event->data->money();
        self::assertNotNull($money);
        self::assertSame('EUR', $money->currency);
    }

    public function testRejectsEmptyId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new WebhookEvent('', 'payment_request.paid', new WebhookPaymentData());
    }

    public function testUnknownTypeIsPreserved(): void
    {
        $event = WebhookEvent::fromArray([
            'id' => 'evt_x',
            'type' => 'payment_request.failed',
            'data' => [],
        ]);

        self::assertFalse($event->isPaymentRequestPaid());
        self::assertNull($event->eventType());
        self::assertNull($event->data->money());
    }
}
