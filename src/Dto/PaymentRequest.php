<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Dto;

use BriizPay\Sdk\Internal\Map;

/**
 * Public shape of a payment request from POST/GET/cancel /v1/payment-requests.
 *
 * Built field by field in briizpay-api V1PaymentRequestsUseCase::present().
 */
final readonly class PaymentRequest
{
    /**
     * @param list<LineItem> $lineItems
     */
    public function __construct(
        public string $id,
        public PaymentStatus $status,
        public Money $amount,
        public string $payUrl,
        public ?string $memo = null,
        public ?string $externalReference = null,
        public ?string $customerName = null,
        public ?string $customerEmail = null,
        public ?string $returnUrl = null,
        public array $lineItems = [],
        public ?string $expiresAt = null,
        public ?string $createdAt = null,
        public ?string $transactionId = null,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $lineItems = [];
        foreach (Map::listOfMaps($data, 'lineItems') as $row) {
            $lineItems[] = LineItem::fromArray($row);
        }

        $currency = Map::optionalString($data, 'currency') ?? 'GBP';

        return new self(
            id: Map::string($data, 'id'),
            status: PaymentStatus::fromApi(Map::string($data, 'status')),
            amount: new Money(Map::int($data, 'amountMinor'), $currency),
            payUrl: Map::string($data, 'payUrl'),
            memo: Map::optionalString($data, 'memo'),
            externalReference: Map::optionalString($data, 'externalReference'),
            customerName: Map::optionalString($data, 'customerName'),
            customerEmail: Map::optionalString($data, 'customerEmail'),
            returnUrl: Map::optionalString($data, 'returnUrl'),
            lineItems: $lineItems,
            expiresAt: Map::optionalString($data, 'expiresAt'),
            createdAt: Map::optionalString($data, 'createdAt'),
            transactionId: Map::optionalString($data, 'transactionId'),
        );
    }

    public function isPaid(): bool
    {
        return $this->status->isPaid();
    }
}
