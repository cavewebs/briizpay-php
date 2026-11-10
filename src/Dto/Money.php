<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Dto;

use BriizPay\Sdk\Internal\Map;

/**
 * An amount in integer minor units plus an ISO 4217 currency code.
 */
final readonly class Money
{
    public string $currency;

    public function __construct(
        public int $amountMinor,
        string $currency,
    ) {
        $code = strtoupper(trim($currency));
        if (preg_match('/^[A-Z]{3}$/', $code) !== 1) {
            throw new \InvalidArgumentException('Currency must be a 3-letter ISO 4217 code.');
        }

        $this->currency = $code;
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data, string $amountKey = 'amountMinor', string $currencyKey = 'currency'): self
    {
        return new self(
            Map::int($data, $amountKey),
            Map::string($data, $currencyKey),
        );
    }

    /**
     * @return array{amountMinor: int, currency: string}
     */
    public function toArray(): array
    {
        return [
            'amountMinor' => $this->amountMinor,
            'currency' => $this->currency,
        ];
    }
}
