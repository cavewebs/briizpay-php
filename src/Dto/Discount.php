<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Dto;

/**
 * A named discount on a receipt, sent as a negative line item.
 *
 * The Magento and WooCommerce plugins put the pay-by-bank saving on the
 * payment as its own line so the bank receipt shows it by name.
 */
final readonly class Discount
{
    public function __construct(
        public string $name,
        public int $amountMinor,
    ) {
        if (trim($this->name) === '') {
            throw new \InvalidArgumentException('Discount name must not be empty.');
        }

        if ($this->amountMinor <= 0) {
            throw new \InvalidArgumentException('Discount amountMinor is the magnitude in pence and must be greater than zero.');
        }
    }

    public function toLineItem(): LineItem
    {
        return new LineItem(
            name: $this->name,
            quantity: 1,
            unitPriceMinor: -$this->amountMinor,
            taxRateBps: 0,
            taxInclusive: true,
        );
    }
}
