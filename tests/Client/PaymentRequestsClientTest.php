<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Client;

use BriizPay\Sdk\Dto\CreatePaymentRequest;
use BriizPay\Sdk\Dto\LineItem;
use BriizPay\Sdk\Dto\Money;
use BriizPay\Sdk\Dto\PaymentStatus;
use BriizPay\Sdk\Sdk;
use BriizPay\Sdk\Tests\Support\ClientFactory;
use BriizPay\Sdk\Tests\Support\Fixture;
use PHPUnit\Framework\TestCase;

final class PaymentRequestsClientTest extends TestCase
{
    public function testCreatePaymentRequestPostsTypedBody(): void
    {
        $pair = ClientFactory::make();
        $pair['http']->enqueue(ClientFactory::jsonResponse(201, Fixture::json('payment-request-created.json')));

        $created = $pair['client']->createPaymentRequest(new CreatePaymentRequest(
            amount: new Money(1485, 'GBP'),
            externalReference: '32',
            customerName: 'Ada Lovelace',
            customerEmail: 'ada@example.com',
            memo: 'Order 32 at Shop',
            returnUrl: 'https://shop.example/checkout/order-received/32/',
            lineItems: [new LineItem('T-shirt', 1, 1485, 2000, true, 'TEE-1')],
        ));

        self::assertSame('pr_01HZX', $created->id);
        self::assertSame(PaymentStatus::Pending, $created->status);
        self::assertSame('https://app.briizpay.com/pay/pl/pr_01HZX', $created->payUrl);
        self::assertNull($created->getTransactionId());
        self::assertCount(1, $pair['http']->requests);

        $request = $pair['http']->requests[0];
        self::assertSame('POST', $request->getMethod());
        self::assertSame('https://api.briizpay.com/v1/payment-requests', (string) $request->getUri());
        self::assertSame('Bearer ' . ClientFactory::liveKey(), $request->getHeaderLine('Authorization'));
        self::assertSame('application/json', $request->getHeaderLine('Accept'));
        self::assertSame(Sdk::USER_AGENT, $request->getHeaderLine('User-Agent'));
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));

        $body = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($body);
        self::assertSame(1485, $body['amountMinor']);
        self::assertSame('GBP', $body['currency']);
        self::assertSame('32', $body['externalReference']);
        self::assertSame('Ada Lovelace', $body['customerName']);
        self::assertIsArray($body['lineItems']);
        self::assertCount(1, $body['lineItems']);
    }

    public function testGetPaymentRequest(): void
    {
        $pair = ClientFactory::make();
        $pair['http']->enqueue(ClientFactory::jsonResponse(200, Fixture::json('payment-request-completed.json')));

        $fetched = $pair['client']->getPaymentRequest('pr_01HZX');

        self::assertTrue($fetched->isPaid());
        self::assertSame('txn_9f', $fetched->getTransactionId());
        $request = $pair['http']->requests[0];
        self::assertSame('GET', $request->getMethod());
        self::assertSame('https://api.briizpay.com/v1/payment-requests/pr_01HZX', (string) $request->getUri());
        self::assertSame('', (string) $request->getBody());
    }

    public function testGetEncodesIdInPath(): void
    {
        $pair = ClientFactory::make();
        $pair['http']->enqueue(ClientFactory::jsonResponse(200, Fixture::json('payment-request-created.json')));

        $pair['client']->getPaymentRequest('pr/slash');

        self::assertSame(
            'https://api.briizpay.com/v1/payment-requests/pr%2Fslash',
            (string) $pair['http']->requests[0]->getUri(),
        );
    }

    public function testCancelSendsEmptyJsonObject(): void
    {
        $pair = ClientFactory::make();
        $pair['http']->enqueue(ClientFactory::jsonResponse(200, Fixture::json('payment-request-cancelled.json')));

        $cancelled = $pair['client']->cancelPaymentRequest('pr_01HZX');

        self::assertSame(PaymentStatus::Cancelled, $cancelled->status);
        self::assertNull($cancelled->getTransactionId());
        $request = $pair['http']->requests[0];
        self::assertSame('POST', $request->getMethod());
        self::assertSame(
            'https://api.briizpay.com/v1/payment-requests/pr_01HZX/cancel',
            (string) $request->getUri(),
        );
        self::assertSame('{}', (string) $request->getBody());
    }
}
