<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Dto;

use BriizPay\Sdk\Dto\CreatePaymentRequest;
use BriizPay\Sdk\Dto\Discount;
use BriizPay\Sdk\Dto\LineItem;
use BriizPay\Sdk\Dto\Money;
use PHPUnit\Framework\TestCase;

final class CreatePaymentRequestTest extends TestCase
{
    public function testSerialisesThePluginCreateBody(): void
    {
        $request = new CreatePaymentRequest(
            amount: new Money(1485, 'GBP'),
            externalReference: '32',
            customerName: 'Ada Lovelace',
            customerEmail: 'ada@example.com',
            memo: 'Order 32 at Shop',
            returnUrl: 'https://shop.example/checkout/order-received/32/',
            lineItems: [
                new LineItem('T-shirt', 1, 1500, 2000, true, 'TEE-1'),
                (new Discount('Pay by bank discount', 15))->toLineItem(),
            ],
        );

        self::assertSame(
            [
                'amountMinor' => 1485,
                'currency' => 'GBP',
                'externalReference' => '32',
                'customerName' => 'Ada Lovelace',
                'customerEmail' => 'ada@example.com',
                'memo' => 'Order 32 at Shop',
                'returnUrl' => 'https://shop.example/checkout/order-received/32/',
                'lineItems' => [
                    [
                        'name' => 'T-shirt',
                        'quantity' => 1,
                        'unitPriceMinor' => 1500,
                        'taxRateBps' => 2000,
                        'taxInclusive' => true,
                        'sku' => 'TEE-1',
                    ],
                    [
                        'name' => 'Pay by bank discount',
                        'quantity' => 1,
                        'unitPriceMinor' => -15,
                        'taxRateBps' => 0,
                        'taxInclusive' => true,
                    ],
                ],
            ],
            $request->toArray(),
        );
    }

    public function testOmitsEmptyOptionalFields(): void
    {
        $request = new CreatePaymentRequest(new Money(500, 'EUR'));

        self::assertSame(
            [
                'amountMinor' => 500,
                'currency' => 'EUR',
            ],
            $request->toArray(),
        );
    }

    public function testRejectsNonPositiveAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CreatePaymentRequest(new Money(0, 'GBP'));
    }

    public function testRejectsMoreThanOneHundredLines(): void
    {
        $lines = [];
        for ($i = 0; $i < 101; ++$i) {
            $lines[] = new LineItem('Item ' . $i, 1, 1);
        }

        $this->expectException(\InvalidArgumentException::class);
        new CreatePaymentRequest(new Money(101, 'GBP'), lineItems: $lines);
    }
}
