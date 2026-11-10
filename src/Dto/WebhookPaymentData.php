<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Dto;

use BriizPay\Sdk\Internal\Map;

/**
 * `data` on a `payment_request.paid` webhook.
 *
 * Magento requires paymentRequestId and externalReference. WooCommerce tries
 * externalReference first, then paymentRequestId. Both re-check amountMinor.
 * After briizpay-api #340 the paid payload also always includes transactionId,
 * status, amountMinor and currency. Those keys stay optional here so older
 * events still parse.
 */
final readonly class WebhookPaymentData
{
    public function __construct(
        public ?string $paymentRequestId = null,
        public ?string $externalReference = null,
        public ?int $amountMinor = null,
        public ?string $currency = null,
        public ?string $transactionId = null,
    ) {
    }

    /**
     * Settled payment id. Always set on current `payment_request.paid` events;
     * null when the key is missing or JSON null.
     */
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            paymentRequestId: Map::optionalString($data, 'paymentRequestId'),
            externalReference: Map::optionalString($data, 'externalReference'),
            amountMinor: Map::optionalInt($data, 'amountMinor'),
            currency: Map::optionalString($data, 'currency'),
            transactionId: Map::optionalString($data, 'transactionId'),
        );
    }

    public function money(): ?Money
    {
        if ($this->amountMinor === null) {
            return null;
        }

        return new Money($this->amountMinor, $this->currency ?? 'GBP');
    }
}
