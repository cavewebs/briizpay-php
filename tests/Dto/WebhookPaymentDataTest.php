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
        self::assertNull($data->getTransactionId());
    }

    public function testTransactionIdPresent(): void
    {
        $data = WebhookPaymentData::fromArray([
            'paymentRequestId' => 'pr_1',
            'status' => 'COMPLETED',
            'amountMinor' => 1499,
            'currency' => 'GBP',
            'transactionId' => 'txn_9f',
        ]);

        self::assertSame('txn_9f', $data->getTransactionId());
    }

    public function testTransactionIdJsonNullParsesAsNull(): void
    {
        $data = WebhookPaymentData::fromArray([
            'paymentRequestId' => 'pr_1',
            'transactionId' => null,
        ]);

        self::assertNull($data->getTransactionId());
    }

    public function testTransactionIdMissingKeyParsesAsNull(): void
    {
        $data = WebhookPaymentData::fromArray([
            'paymentRequestId' => 'pr_1',
        ]);

        self::assertNull($data->getTransactionId());
    }
}
