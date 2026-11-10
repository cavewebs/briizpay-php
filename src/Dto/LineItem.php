<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Dto;

use BriizPay\Sdk\Internal\Map;

/**
 * One receipt line on a payment request.
 *
 * unitPriceMinor is signed: a shop discount is a negative line so the receipt
 * shows it by name. Lines must add up to amountMinor or BriizPay refuses the
 * request with LINE_ITEMS_TOTAL_MISMATCH.
 */
final readonly class LineItem
{
    public string $name;

    public ?string $sku;

    public ?string $description;

    public function __construct(
        string $name,
        public int|float $quantity,
        public int $unitPriceMinor,
        public int $taxRateBps = 0,
        public bool $taxInclusive = true,
        ?string $sku = null,
        ?string $description = null,
    ) {
        $trimmed = trim($name);
        if ($trimmed === '') {
            throw new \InvalidArgumentException('Line item name must not be empty.');
        }

        if (mb_strlen($trimmed) > 140) {
            throw new \InvalidArgumentException('Line item name must be at most 140 characters.');
        }

        if (is_float($this->quantity) && (!is_finite($this->quantity) || $this->quantity <= 0)) {
            throw new \InvalidArgumentException('Line item quantity must be greater than zero.');
        }

        if (is_int($this->quantity) && $this->quantity <= 0) {
            throw new \InvalidArgumentException('Line item quantity must be greater than zero.');
        }

        if ($this->taxRateBps < 0 || $this->taxRateBps > 10000) {
            throw new \InvalidArgumentException('taxRateBps must be between 0 and 10000.');
        }

        $skuValue = $sku === null ? null : trim($sku);
        if ($skuValue === '') {
            $skuValue = null;
        }

        $descriptionValue = $description === null ? null : trim($description);
        if ($descriptionValue === '') {
            $descriptionValue = null;
        }

        if ($skuValue !== null && mb_strlen($skuValue) > 64) {
            throw new \InvalidArgumentException('SKU must be at most 64 characters.');
        }

        if ($descriptionValue !== null && mb_strlen($descriptionValue) > 280) {
            throw new \InvalidArgumentException('Description must be at most 280 characters.');
        }

        $this->name = $trimmed;
        $this->sku = $skuValue;
        $this->description = $descriptionValue;
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            Map::string($data, 'name'),
            Map::number($data, 'quantity'),
            Map::int($data, 'unitPriceMinor'),
            Map::optionalInt($data, 'taxRateBps') ?? 0,
            Map::bool($data, 'taxInclusive', true),
            Map::optionalString($data, 'sku'),
            Map::optionalString($data, 'description'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $quantity = $this->quantity;
        if (is_float($quantity) && floor($quantity) === $quantity) {
            $quantity = (int) $quantity;
        }

        $payload = [
            'name' => $this->name,
            'quantity' => $quantity,
            'unitPriceMinor' => $this->unitPriceMinor,
            'taxRateBps' => $this->taxRateBps,
            'taxInclusive' => $this->taxInclusive,
        ];

        if ($this->sku !== null) {
            $payload['sku'] = $this->sku;
        }

        if ($this->description !== null) {
            $payload['description'] = $this->description;
        }

        return $payload;
    }
}
