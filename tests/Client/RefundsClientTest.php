<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Client;

use BriizPay\Sdk\Dto\CreateRefund;
use BriizPay\Sdk\Dto\RefundMode;
use BriizPay\Sdk\Dto\RefundStatus;
use BriizPay\Sdk\Tests\Support\ClientFactory;
use BriizPay\Sdk\Tests\Support\Fixture;
use PHPUnit\Framework\TestCase;

final class RefundsClientTest extends TestCase
{
    public function testCreateRefundKeepsEnvelopeInstruction(): void
    {
        $pair = ClientFactory::make();
        $pair['http']->enqueue(ClientFactory::jsonResponse(200, Fixture::json('refund-pending.json')));

        $refund = $pair['client']->createRefund(new CreateRefund(
            paymentRequestId: 'pr_1',
            amountMinor: 1995,
            externalReference: 'wc_refund_7',
            reason: 'Returned',
        ));

        self::assertSame('rf_1', $refund->id);
        self::assertSame(RefundStatus::Pending, $refund->status);
        self::assertSame(RefundMode::Manual, $refund->mode);
        self::assertNotNull($refund->instruction());

        $request = $pair['http']->requests[0];
        self::assertSame('POST', $request->getMethod());
        self::assertSame('https://api.briizpay.com/v1/refunds', (string) $request->getUri());
        $body = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(
            [
                'paymentRequestId' => 'pr_1',
                'amountMinor' => 1995,
                'reason' => 'Returned',
                'externalReference' => 'wc_refund_7',
            ],
            $body,
        );
    }

    public function testCompletedAutoRefund(): void
    {
        $pair = ClientFactory::make();
        $pair['http']->enqueue(ClientFactory::jsonResponse(200, Fixture::json('refund-completed.json')));

        $refund = $pair['client']->createRefund(new CreateRefund('pr_1', 1995, 'magento_creditmemo_000000042'));

        self::assertSame(RefundStatus::Completed, $refund->status);
        self::assertSame(RefundMode::Auto, $refund->mode);
    }
}
