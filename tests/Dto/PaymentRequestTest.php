<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Dto;

use BriizPay\Sdk\Dto\PaymentRequest;
use BriizPay\Sdk\Dto\PaymentStatus;
use BriizPay\Sdk\Tests\Support\Fixture;
use PHPUnit\Framework\TestCase;

final class PaymentRequestTest extends TestCase
{
    public function testFromCreatedFixture(): void
    {
        $payload = Fixture::json('payment-request-created.json');
        self::assertIsArray($payload['data']);
        /** @var array<mixed> $data */
        $data = $payload['data'];
        $request = PaymentRequest::fromArray($data);

        self::assertSame('pr_01HZX', $request->id);
        self::assertSame(PaymentStatus::Pending, $request->status);
        self::assertSame(1485, $request->amount->amountMinor);
        self::assertSame('GBP', $request->amount->currency);
        self::assertSame('Order 32 at Shop', $request->memo);
        self::assertSame('32', $request->externalReference);
        self::assertSame('Ada Lovelace', $request->customerName);
        self::assertSame('ada@example.com', $request->customerEmail);
        self::assertSame('https://shop.example/checkout/order-received/32/', $request->returnUrl);
        self::assertCount(2, $request->lineItems);
        self::assertSame('T-shirt', $request->lineItems[0]->name);
        self::assertSame(-15, $request->lineItems[1]->unitPriceMinor);
        self::assertSame('https://app.briizpay.com/pay/pl/pr_01HZX', $request->payUrl);
        self::assertSame('2026-11-10T14:00:00.000Z', $request->expiresAt);
        self::assertSame('2026-11-10T13:00:00.000Z', $request->createdAt);
        self::assertNull($request->getTransactionId());
        self::assertFalse($request->isPaid());
    }

    public function testCompletedIncludesTransactionId(): void
    {
        $payload = Fixture::json('payment-request-completed.json');
        self::assertIsArray($payload['data']);
        /** @var array<mixed> $data */
        $data = $payload['data'];
        $request = PaymentRequest::fromArray($data);

        self::assertSame(PaymentStatus::Completed, $request->status);
        self::assertTrue($request->isPaid());
        self::assertSame('txn_9f', $request->getTransactionId());
    }

    public function testTransactionIdJsonNullParsesAsNull(): void
    {
        $request = PaymentRequest::fromArray([
            'id' => 'pr_1',
            'status' => 'PENDING',
            'amountMinor' => 100,
            'currency' => 'GBP',
            'payUrl' => 'https://example.test/pay',
            'transactionId' => null,
        ]);

        self::assertNull($request->getTransactionId());
    }

    public function testTransactionIdMissingKeyParsesAsNull(): void
    {
        $request = PaymentRequest::fromArray([
            'id' => 'pr_1',
            'status' => 'PENDING',
            'amountMinor' => 100,
            'currency' => 'GBP',
            'payUrl' => 'https://example.test/pay',
        ]);

        self::assertNull($request->getTransactionId());
    }

    public function testCancelledAndExpiredStatuses(): void
    {
        $cancelledPayload = Fixture::json('payment-request-cancelled.json');
        self::assertIsArray($cancelledPayload['data']);
        /** @var array<mixed> $cancelledData */
        $cancelledData = $cancelledPayload['data'];
        $cancelled = PaymentRequest::fromArray($cancelledData);
        self::assertSame(PaymentStatus::Cancelled, $cancelled->status);
        self::assertTrue($cancelled->status->isTerminalUnpaid());
        self::assertNull($cancelled->getTransactionId());

        $expiredPayload = Fixture::json('payment-request-expired.json');
        self::assertIsArray($expiredPayload['data']);
        /** @var array<mixed> $expiredData */
        $expiredData = $expiredPayload['data'];
        $expired = PaymentRequest::fromArray($expiredData);
        self::assertSame(PaymentStatus::Expired, $expired->status);
        self::assertNull($expired->customerName);
    }

    public function testFailedStatusFromApi(): void
    {
        $request = PaymentRequest::fromArray([
            'id' => 'pr_fail',
            'status' => 'FAILED',
            'amountMinor' => 100,
            'currency' => 'GBP',
            'payUrl' => 'https://app.briizpay.com/pay/pl/pr_fail',
        ]);

        self::assertSame(PaymentStatus::Failed, $request->status);
        self::assertTrue($request->status->isTerminalUnpaid());
        self::assertFalse($request->isPaid());
    }

    public function testDefaultsCurrencyToGbpWhenMissing(): void
    {
        $request = PaymentRequest::fromArray([
            'id' => 'pr_1',
            'status' => 'PENDING',
            'amountMinor' => 100,
            'payUrl' => 'https://example.test/pay',
        ]);

        self::assertSame('GBP', $request->amount->currency);
    }

    public function testUnknownStatusIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown payment status "BOGUS".');

        PaymentRequest::fromArray([
            'id' => 'pr_1',
            'status' => 'BOGUS',
            'amountMinor' => 100,
            'currency' => 'GBP',
            'payUrl' => 'https://example.test/pay',
        ]);
    }
}
