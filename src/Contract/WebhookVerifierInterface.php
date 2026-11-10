<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Contract;

use BriizPay\Sdk\Dto\WebhookEvent;

interface WebhookVerifierInterface
{
    /**
     * Header: Briizpay-Signature: t=<unix>,v1=<hex HMAC-SHA256 of "timestamp.body">
     */
    public function verify(string $payload, ?string $signatureHeader, string $secret, ?int $now = null): bool;

    public function parse(string $payload, ?string $signatureHeader, string $secret, ?int $now = null): WebhookEvent;
}
