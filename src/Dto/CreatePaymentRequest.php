<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Dto;

/**
 * Body of POST /v1/payment-requests.
 *
 * Matches what the Magento and WooCommerce clients send: amount in pence,
 * currency, shop order reference, customer, memo, return URL, and receipt lines.
 */
final readonly class CreatePaymentRequest
{
    /**
     * @param list<LineItem> $lineItems
     */
    public function __construct(
        public Money $amount,
        public ?string $externalReference = null,
        public ?string $customerName = null,
        public ?string $customerEmail = null,
        public ?string $memo = null,
        public ?string $returnUrl = null,
        public array $lineItems = [],
    ) {
        if ($this->amount->amountMinor <= 0) {
            throw new \InvalidArgumentException('amountMinor must be a whole number of pence greater than zero.');
        }

        if (count($this->lineItems) > 100) {
            throw new \InvalidArgumentException('A payment request may carry at most 100 line items.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'amountMinor' => $this->amount->amountMinor,
            'currency' => $this->amount->currency,
        ];

        if ($this->externalReference !== null && $this->externalReference !== '') {
            $payload['externalReference'] = $this->externalReference;
        }

        if ($this->customerName !== null && $this->customerName !== '') {
            $payload['customerName'] = $this->customerName;
        }

        if ($this->customerEmail !== null && $this->customerEmail !== '') {
            $payload['customerEmail'] = $this->customerEmail;
        }

        if ($this->memo !== null && $this->memo !== '') {
            $payload['memo'] = $this->memo;
        }

        if ($this->returnUrl !== null && $this->returnUrl !== '') {
            $payload['returnUrl'] = $this->returnUrl;
        }

        if ($this->lineItems !== []) {
            $payload['lineItems'] = array_map(
                static fn (LineItem $item): array => $item->toArray(),
                $this->lineItems,
            );
        }

        return $payload;
    }
}
