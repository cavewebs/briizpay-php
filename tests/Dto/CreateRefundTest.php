<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Dto;

use BriizPay\Sdk\Dto\CreateRefund;
use PHPUnit\Framework\TestCase;

final class CreateRefundTest extends TestCase
{
    public function testSerialisesThePluginRefundBody(): void
    {
        $request = new CreateRefund(
            paymentRequestId: 'pr_1',
            amountMinor: 1995,
            externalReference: 'wc_refund_7',
            reason: 'Returned',
        );

        self::assertSame(
            [
                'paymentRequestId' => 'pr_1',
                'amountMinor' => 1995,
                'reason' => 'Returned',
                'externalReference' => 'wc_refund_7',
            ],
            $request->toArray(),
        );
    }

    public function testReasonDefaultsToEmptyString(): void
    {
        $request = new CreateRefund('pr_1', 100, 'magento_creditmemo_000000042');

        self::assertSame('', $request->toArray()['reason']);
    }

    public function testRejectsEmptyPaymentRequestId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CreateRefund(' ', 100, 'ref');
    }

    public function testRejectsNonPositiveAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CreateRefund('pr_1', 0, 'ref');
    }

    public function testRejectsEmptyExternalReference(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CreateRefund('pr_1', 100, ' ');
    }
}
