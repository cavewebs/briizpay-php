<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Dto;

use BriizPay\Sdk\Dto\Refund;
use BriizPay\Sdk\Dto\RefundMode;
use BriizPay\Sdk\Dto\RefundStatus;
use BriizPay\Sdk\Tests\Support\Fixture;
use PHPUnit\Framework\TestCase;

final class RefundTest extends TestCase
{
    public function testKeepsEnvelopeModeAndMessage(): void
    {
        $refund = Refund::fromArray(Fixture::json('refund-pending.json'));

        self::assertSame('rf_1', $refund->id);
        self::assertSame('pr_1', $refund->paymentRequestId);
        self::assertSame('txn_9f', $refund->getTransactionId());
        self::assertSame(1995, $refund->amountMinor);
        self::assertSame(RefundStatus::Pending, $refund->status);
        self::assertSame('Returned', $refund->reason);
        self::assertSame('wc_refund_7', $refund->externalReference);
        self::assertSame('2026-11-10T13:05:00.000Z', $refund->createdAt);
        self::assertNull($refund->completedAt);
        self::assertSame(RefundMode::Manual, $refund->mode);
        self::assertSame('Send the refund from your bank, then mark it complete in BriizPay.', $refund->message);
        self::assertSame($refund->message, $refund->instruction());
        self::assertFalse($refund->status->isSettled());
    }

    public function testCompletedAutoRefund(): void
    {
        $refund = Refund::fromArray(Fixture::json('refund-completed.json'));

        self::assertSame(RefundStatus::Completed, $refund->status);
        self::assertTrue($refund->status->isSettled());
        self::assertSame(RefundMode::Auto, $refund->mode);
        self::assertSame('2026-11-10T13:05:01.000Z', $refund->completedAt);
        self::assertSame('Refunded. The money is on its way back to the customer.', $refund->instruction());
    }

    public function testUnwrappedDataStillParses(): void
    {
        $payload = Fixture::json('refund-pending.json');
        self::assertIsArray($payload['data']);
        /** @var array<mixed> $data */
        $data = $payload['data'];
        $refund = Refund::fromArray($data);

        self::assertSame('rf_1', $refund->id);
        self::assertNull($refund->mode);
        self::assertNull($refund->message);
    }

    public function testHeldStatus(): void
    {
        $refund = Refund::fromArray([
            'id' => 'rf_h',
            'paymentRequestId' => 'pr_1',
            'transactionId' => 'txn_9f',
            'amountMinor' => 50,
            'status' => 'HELD',
        ]);

        self::assertSame(RefundStatus::Held, $refund->status);
        self::assertSame('txn_9f', $refund->getTransactionId());
    }

    public function testFailedStatus(): void
    {
        $refund = Refund::fromArray([
            'id' => 'rf_f',
            'paymentRequestId' => 'pr_1',
            'amountMinor' => 50,
            'status' => 'FAILED',
        ]);

        self::assertSame(RefundStatus::Failed, $refund->status);
        self::assertNull($refund->getTransactionId());
    }

    public function testTransactionIdPresent(): void
    {
        $refund = Refund::fromArray(Fixture::json('refund-completed.json'));

        self::assertSame('txn_9f', $refund->getTransactionId());
    }

    public function testTransactionIdJsonNullParsesAsNull(): void
    {
        $refund = Refund::fromArray([
            'id' => 'rf_n',
            'paymentRequestId' => 'pr_1',
            'amountMinor' => 50,
            'status' => 'PENDING',
            'transactionId' => null,
        ]);

        self::assertNull($refund->getTransactionId());
    }

    public function testTransactionIdMissingKeyParsesAsNull(): void
    {
        $refund = Refund::fromArray([
            'id' => 'rf_m',
            'paymentRequestId' => 'pr_1',
            'amountMinor' => 50,
            'status' => 'PENDING',
        ]);

        self::assertNull($refund->getTransactionId());
    }
}
