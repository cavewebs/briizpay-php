<?php

declare(strict_types=1);

namespace BriizPay\Sdk;

use BriizPay\Sdk\Contract\WebhookVerifierInterface;
use BriizPay\Sdk\Dto\CreatePaymentRequest;
use BriizPay\Sdk\Dto\CreateRefund;
use BriizPay\Sdk\Dto\PaymentRequest;
use BriizPay\Sdk\Dto\Refund;
use BriizPay\Sdk\Dto\WebhookEvent;
use BriizPay\Sdk\Exception\UnexpectedResponseException;
use BriizPay\Sdk\Http\HttpTransport;
use BriizPay\Sdk\Webhook\WebhookSignature;
use Psr\Http\Client\ClientInterface as HttpClient;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final readonly class Client implements ClientInterface
{
    private HttpTransport $transport;

    private WebhookVerifierInterface $webhooks;

    public function __construct(
        Config $config,
        HttpClient $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        ?WebhookVerifierInterface $webhooks = null,
    ) {
        $this->transport = new HttpTransport($config, $httpClient, $requestFactory, $streamFactory);
        $this->webhooks = $webhooks ?? new WebhookSignature();
    }

    public function createPaymentRequest(CreatePaymentRequest $request): PaymentRequest
    {
        $envelope = $this->transport->request('POST', '/v1/payment-requests', $request->toArray());

        return PaymentRequest::fromArray($this->data($envelope));
    }

    public function getPaymentRequest(string $id): PaymentRequest
    {
        $envelope = $this->transport->request('GET', '/v1/payment-requests/' . rawurlencode($id));

        return PaymentRequest::fromArray($this->data($envelope));
    }

    public function cancelPaymentRequest(string $id): PaymentRequest
    {
        $envelope = $this->transport->request(
            'POST',
            '/v1/payment-requests/' . rawurlencode($id) . '/cancel',
            new \stdClass(),
        );

        return PaymentRequest::fromArray($this->data($envelope));
    }

    public function createRefund(CreateRefund $request): Refund
    {
        $envelope = $this->transport->request('POST', '/v1/refunds', $request->toArray());

        return Refund::fromArray($envelope);
    }

    public function verify(string $payload, ?string $signatureHeader, string $secret, ?int $now = null): bool
    {
        return $this->webhooks->verify($payload, $signatureHeader, $secret, $now);
    }

    public function parse(string $payload, ?string $signatureHeader, string $secret, ?int $now = null): WebhookEvent
    {
        return $this->webhooks->parse($payload, $signatureHeader, $secret, $now);
    }

    /**
     * @param array<mixed> $envelope
     *
     * @return array<mixed>
     */
    private function data(array $envelope): array
    {
        if (isset($envelope['data']) && is_array($envelope['data'])) {
            return $envelope['data'];
        }

        if ($envelope !== []) {
            return $envelope;
        }

        throw new UnexpectedResponseException('BriizPay returned an empty payload.');
    }
}
