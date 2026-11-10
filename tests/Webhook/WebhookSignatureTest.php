<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Webhook;

use BriizPay\Sdk\Dto\WebhookEventType;
use BriizPay\Sdk\Exception\AuthenticationException;
use BriizPay\Sdk\Exception\ValidationException;
use BriizPay\Sdk\Tests\Support\Fixture;
use BriizPay\Sdk\Webhook\WebhookSignature;
use PHPUnit\Framework\TestCase;

final class WebhookSignatureTest extends TestCase
{
    private const SECRET = 'whsec_' . 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';

    private const MAGENTO_TIMESTAMP = 1788780000;

    public function testAcceptsAValidSignature(): void
    {
        $payload = Fixture::raw('webhook-paid.json');
        $webhooks = new WebhookSignature();
        $header = $webhooks->sign($payload, self::SECRET, self::MAGENTO_TIMESTAMP);

        self::assertTrue($webhooks->verify($payload, $header, self::SECRET, self::MAGENTO_TIMESTAMP));
        $event = $webhooks->parse($payload, $header, self::SECRET, self::MAGENTO_TIMESTAMP);
        self::assertSame('evt_magento', $event->id);
        self::assertSame(WebhookEventType::PaymentRequestPaid, $event->eventType());
    }

    public function testMagentoVectorStyleHeader(): void
    {
        $payload = Fixture::raw('webhook-paid.json');
        $webhooks = new WebhookSignature();
        $expected = hash_hmac('sha256', self::MAGENTO_TIMESTAMP . '.' . $payload, self::SECRET);
        $header = 't=' . self::MAGENTO_TIMESTAMP . ',v1=' . $expected;

        self::assertTrue($webhooks->verify($payload, $header, self::SECRET, self::MAGENTO_TIMESTAMP));
    }

    public function testRejectsTamperedPayload(): void
    {
        $payload = Fixture::raw('webhook-paid.json');
        $webhooks = new WebhookSignature();
        $header = $webhooks->sign($payload, self::SECRET, self::MAGENTO_TIMESTAMP);

        self::assertFalse($webhooks->verify($payload . ' ', $header, self::SECRET, self::MAGENTO_TIMESTAMP));
    }

    public function testRejectsStaleTimestamp(): void
    {
        $payload = Fixture::raw('webhook-paid.json');
        $webhooks = new WebhookSignature();
        $header = $webhooks->sign($payload, self::SECRET, self::MAGENTO_TIMESTAMP);

        self::assertFalse(
            $webhooks->verify($payload, $header, self::SECRET, self::MAGENTO_TIMESTAMP + WebhookSignature::TOLERANCE_SECONDS + 1),
        );
    }

    public function testRejectsMissingHeader(): void
    {
        $webhooks = new WebhookSignature();

        self::assertFalse($webhooks->verify('{}', null, self::SECRET));
        self::assertFalse($webhooks->verify('{}', '', self::SECRET));
        self::assertFalse($webhooks->verify('{}', 't=1,v1=abc', ''));
    }

    public function testParseThrowsOnBadSignature(): void
    {
        $this->expectException(AuthenticationException::class);
        (new WebhookSignature())->parse('{}', 't=1,v1=nope', self::SECRET, 1);
    }

    public function testParseThrowsOnNonJson(): void
    {
        $webhooks = new WebhookSignature();
        $payload = 'not-json';
        $header = $webhooks->sign($payload, self::SECRET, 100);

        $this->expectException(ValidationException::class);
        $webhooks->parse($payload, $header, self::SECRET, 100);
    }

    public function testParseThrowsOnJsonArray(): void
    {
        $webhooks = new WebhookSignature();
        $payload = '[]';
        $header = $webhooks->sign($payload, self::SECRET, 100);

        $this->expectException(ValidationException::class);
        $webhooks->parse($payload, $header, self::SECRET, 100);
    }

    public function testSignFormat(): void
    {
        $header = (new WebhookSignature())->sign('{}', self::SECRET, 100);

        self::assertStringStartsWith('t=100,v1=', $header);
        self::assertSame(64, strlen(substr($header, strlen('t=100,v1='))));
    }
}
