<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Dto;

use BriizPay\Sdk\Internal\Map;

/**
 * Public shape of POST /v1/refunds.
 *
 * The API returns `{ data, mode, msg }`. The Magento and WooCommerce clients
 * unwrap `data`, which drops `mode` and `msg`. This DTO keeps all three so a
 * shop admin can be told whether they still have to send the money.
 */
final readonly class Refund
{
    public function __construct(
        public string $id,
        public string $paymentRequestId,
        public int $amountMinor,
        public RefundStatus $status,
        public ?string $reason = null,
        public ?string $externalReference = null,
        public ?string $createdAt = null,
        public ?string $completedAt = null,
        public ?RefundMode $mode = null,
        public ?string $message = null,
        public ?string $transactionId = null,
    ) {
    }

    /**
     * Id of the payment this refunds. Present on current POST /v1/refunds
     * (and GET /v1/payment-requests/{id}/refunds) responses; null when the
     * key is missing or JSON null.
     */
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    /**
     * @param array<mixed> $envelope decoded JSON, either `{data, mode, msg}` or the inner row
     */
    public static function fromArray(array $envelope): self
    {
        $data = $envelope;
        if (isset($envelope['data']) && is_array($envelope['data'])) {
            $data = $envelope['data'];
        }

        $modeValue = $envelope['mode'] ?? $data['mode'] ?? null;
        $mode = is_string($modeValue) ? RefundMode::tryFrom($modeValue) : null;

        $message = $envelope['msg'] ?? $data['msg'] ?? null;
        if (!is_string($message) || $message === '') {
            $message = null;
        }

        return new self(
            id: Map::string($data, 'id'),
            paymentRequestId: Map::string($data, 'paymentRequestId'),
            amountMinor: Map::int($data, 'amountMinor'),
            status: RefundStatus::fromApi(Map::string($data, 'status')),
            reason: Map::optionalString($data, 'reason'),
            externalReference: Map::optionalString($data, 'externalReference'),
            createdAt: Map::optionalString($data, 'createdAt'),
            completedAt: Map::optionalString($data, 'completedAt'),
            mode: $mode,
            message: $message,
            transactionId: Map::optionalString($data, 'transactionId'),
        );
    }

    /**
     * Merchant-facing instruction from the API (`msg`), if present.
     */
    public function instruction(): ?string
    {
        return $this->message;
    }
}
