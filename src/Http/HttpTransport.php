<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Http;

use BriizPay\Sdk\Config;
use BriizPay\Sdk\Exception\AuthenticationException;
use BriizPay\Sdk\Exception\BriizPayException;
use BriizPay\Sdk\Exception\ConflictException;
use BriizPay\Sdk\Exception\NetworkException;
use BriizPay\Sdk\Exception\NotFoundException;
use BriizPay\Sdk\Exception\RateLimitException;
use BriizPay\Sdk\Exception\ServerException;
use BriizPay\Sdk\Exception\UnexpectedResponseException;
use BriizPay\Sdk\Exception\ValidationException;
use BriizPay\Sdk\Sdk;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as HttpClient;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * One HTTP round trip against /v1.
 *
 * @internal
 */
final readonly class HttpTransport
{
    public function __construct(
        private Config $config,
        private HttpClient $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
    ) {
    }

    /**
     * @param array<string, mixed>|\stdClass|null $body
     *
     * @return array<mixed>
     */
    public function request(string $method, string $path, array|\stdClass|null $body = null): array
    {
        $uri = $this->config->baseUrl . $path;
        $request = $this->requestFactory->createRequest($method, $uri)
            ->withHeader('Authorization', 'Bearer ' . $this->config->apiKey)
            ->withHeader('Accept', 'application/json')
            ->withHeader('User-Agent', Sdk::USER_AGENT);

        if ($body !== null) {
            try {
                $encoded = json_encode($body, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new UnexpectedResponseException('Could not encode request JSON.', 0, previous: $e);
            }

            $request = $request
                ->withHeader('Content-Type', 'application/json')
                ->withBody($this->streamFactory->createStream($encoded));
        }

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (NetworkExceptionInterface $e) {
            throw new NetworkException('Could not reach BriizPay. Please try again.', 0, previous: $e);
        } catch (ClientExceptionInterface $e) {
            throw new NetworkException('Could not reach BriizPay. Please try again.', 0, previous: $e);
        }

        return $this->decode($response);
    }

    /**
     * @return array<mixed>
     */
    private function decode(ResponseInterface $response): array
    {
        $status = $response->getStatusCode();
        $raw = (string) $response->getBody();

        $parsed = [];
        if ($raw !== '') {
            try {
                $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                $decoded = null;
            }

            if (is_array($decoded)) {
                $parsed = $decoded;
            }
        }

        if ($status >= 200 && $status < 300) {
            return $parsed;
        }

        throw $this->toException($status, $parsed, $response);
    }

    /**
     * @param array<mixed> $parsed
     */
    private function toException(int $status, array $parsed, ResponseInterface $response): BriizPayException
    {
        $message = 'BriizPay could not take this payment right now.';
        if (isset($parsed['msg']) && is_string($parsed['msg']) && $parsed['msg'] !== '') {
            $message = $parsed['msg'];
        } elseif (isset($parsed['detail']) && is_string($parsed['detail']) && $parsed['detail'] !== '') {
            $message = $parsed['detail'];
        }

        $code = null;
        if (isset($parsed['code']) && is_string($parsed['code']) && $parsed['code'] !== '') {
            $code = $parsed['code'];
        }

        return match (true) {
            $status === 401, $status === 403 => new AuthenticationException($message, $status, $code),
            $status === 404 => new NotFoundException($message, $status, $code),
            $status === 409 => new ConflictException($message, $status, $code),
            $status === 422, $status === 400 => new ValidationException($message, $status, $code),
            $status === 429 => new RateLimitException(
                $message,
                $status,
                $code,
                $this->retryAfter($response),
            ),
            $status >= 500 => new ServerException($message, $status, $code),
            default => new BriizPayException($message, $status, $code),
        };
    }

    private function retryAfter(ResponseInterface $response): ?int
    {
        if (!$response->hasHeader('Retry-After')) {
            return null;
        }

        $value = $response->getHeaderLine('Retry-After');
        if (ctype_digit($value)) {
            return (int) $value;
        }

        return null;
    }
}
