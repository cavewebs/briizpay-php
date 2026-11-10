<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Dto;

/**
 * Body of POST /v1/refunds.
 *
 * The plugins report a shop refund; they do not move money. externalReference
 * is the shop's own refund id so a retry returns the first row instead of a second.
 */
final readonly class CreateRefund
{
    public function __construct(
        public string $paymentRequestId,
        public int $amountMinor,
        public string $externalReference,
        public string $reason = '',
    ) {
        if (trim($this->paymentRequestId) === '') {
            throw new \InvalidArgumentException('paymentRequestId is required.');
        }

        if ($this->amountMinor <= 0) {
            throw new \InvalidArgumentException('amountMinor must be a whole number of pence greater than zero.');
        }

        if (trim($this->externalReference) === '') {
            throw new \InvalidArgumentException('externalReference is required so a retry cannot refund twice.');
        }
    }

    /**
     * @return array{paymentRequestId: string, amountMinor: int, reason: string, externalReference: string}
     */
    public function toArray(): array
    {
        return [
            'paymentRequestId' => $this->paymentRequestId,
            'amountMinor' => $this->amountMinor,
            'reason' => $this->reason,
            'externalReference' => $this->externalReference,
        ];
    }
}
