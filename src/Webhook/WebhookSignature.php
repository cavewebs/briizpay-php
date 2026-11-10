<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Webhook;

use BriizPay\Sdk\Contract\WebhookVerifierInterface;
use BriizPay\Sdk\Dto\WebhookEvent;
use BriizPay\Sdk\Exception\AuthenticationException;
use BriizPay\Sdk\Exception\ValidationException;

/**
 * Checking that a webhook really came from BriizPay.
 *
 * Same scheme as briizpay-api src/lib/webhook-signature.ts and both plugins:
 * HMAC-SHA256 over "timestamp.body", header `Briizpay-Signature: t=,v1=`.
 */
final readonly class WebhookSignature implements WebhookVerifierInterface
{
    public const HEADER = 'Briizpay-Signature';

    public const TOLERANCE_SECONDS = 300;

    public function verify(string $payload, ?string $signatureHeader, string $secret, ?int $now = null): bool
    {
        if ($signatureHeader === null || $signatureHeader === '' || $secret === '') {
            return false;
        }

        $now = $now ?? time();

        $timestamp = null;
        $provided = null;
        foreach (explode(',', $signatureHeader) as $part) {
            $pair = explode('=', trim($part), 2);
            if (count($pair) !== 2) {
                continue;
            }

            if ($pair[0] === 't') {
                $timestamp = $pair[1];
            } elseif ($pair[0] === 'v1') {
                $provided = $pair[1];
            }
        }

        if ($timestamp === null || $provided === null || !ctype_digit($timestamp)) {
            return false;
        }

        if (abs($now - (int) $timestamp) > self::TOLERANCE_SECONDS) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

        return hash_equals($expected, $provided);
    }

    public function parse(string $payload, ?string $signatureHeader, string $secret, ?int $now = null): WebhookEvent
    {
        if (!$this->verify($payload, $signatureHeader, $secret, $now)) {
            throw new AuthenticationException('Webhook rejected: bad signature.', 401);
        }

        try {
            $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new ValidationException('Webhook payload is not JSON.', 400, previous: $e);
        }

        if (!is_array($decoded)) {
            throw new ValidationException('Webhook payload must be a JSON object.', 400);
        }

        try {
            return WebhookEvent::fromArray($decoded);
        } catch (\InvalidArgumentException $e) {
            throw new ValidationException($e->getMessage(), 400, previous: $e);
        }
    }

    public function sign(string $payload, string $secret, int $timestamp): string
    {
        $mac = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

        return 't=' . $timestamp . ',v1=' . $mac;
    }
}
