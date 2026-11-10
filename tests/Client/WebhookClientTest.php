<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Client;

use BriizPay\Sdk\Exception\AuthenticationException;
use BriizPay\Sdk\Tests\Support\ClientFactory;
use BriizPay\Sdk\Tests\Support\Fixture;
use BriizPay\Sdk\Webhook\WebhookSignature;
use PHPUnit\Framework\TestCase;

final class WebhookClientTest extends TestCase
{
    public function testClientDelegatesVerifyAndParse(): void
    {
        $pair = ClientFactory::make();
        $payload = Fixture::raw('webhook-paid.json');
        $now = 1_700_000_000;
        $header = (new WebhookSignature())->sign($payload, 'secret', $now);

        self::assertTrue($pair['client']->verify($payload, $header, 'secret', $now));
        $event = $pair['client']->parse($payload, $header, 'secret', $now);
        self::assertSame('evt_magento', $event->id);
        self::assertSame('req_1', $event->data->paymentRequestId);
        self::assertSame('txn_9f', $event->data->getTransactionId());
    }

    public function testClientParseRejectsBadSignature(): void
    {
        $pair = ClientFactory::make();

        $this->expectException(AuthenticationException::class);
        $pair['client']->parse(Fixture::raw('webhook-paid.json'), 't=1,v1=nope', 'secret', 1);
    }
}
