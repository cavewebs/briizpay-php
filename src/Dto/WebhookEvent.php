<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Dto;

use BriizPay\Sdk\Internal\Map;

/**
 * Outbound webhook body. v1 delivers `payment_request.paid`.
 *
 * Delivery is at-least-once: callers must ignore an event id they have already applied.
 */
final readonly class WebhookEvent
{
    public function __construct(
        public string $id,
        public string $type,
        public WebhookPaymentData $data,
    ) {
        if ($this->id === '') {
            throw new \InvalidArgumentException('Webhook event id is required.');
        }

        if ($this->type === '') {
            throw new \InvalidArgumentException('Webhook event type is required.');
        }
    }

    /**
     * @param array<mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            Map::string($payload, 'id'),
            Map::string($payload, 'type'),
            WebhookPaymentData::fromArray(Map::optionalMap($payload, 'data')),
        );
    }

    public function isPaymentRequestPaid(): bool
    {
        return $this->type === WebhookEventType::PaymentRequestPaid->value;
    }

    public function eventType(): ?WebhookEventType
    {
        return WebhookEventType::tryFrom($this->type);
    }
}
