<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Client;

use BriizPay\Sdk\Exception\AuthenticationException;
use BriizPay\Sdk\Exception\BriizPayException;
use BriizPay\Sdk\Exception\ConflictException;
use BriizPay\Sdk\Exception\NetworkException;
use BriizPay\Sdk\Exception\NotFoundException;
use BriizPay\Sdk\Exception\RateLimitException;
use BriizPay\Sdk\Exception\ServerException;
use BriizPay\Sdk\Exception\UnexpectedResponseException;
use BriizPay\Sdk\Exception\ValidationException;
use BriizPay\Sdk\Tests\Support\ClientFactory;
use BriizPay\Sdk\Tests\Support\Fixture;
use BriizPay\Sdk\Tests\Support\StubClientException;
use BriizPay\Sdk\Tests\Support\StubNetworkException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class ErrorMappingTest extends TestCase
{
    public function test401IsAuthentication(): void
    {
        $this->expectStatus(401, Fixture::json('error-401.json'), AuthenticationException::class, 'Invalid API key');
    }

    public function test403IsAuthentication(): void
    {
        $this->expectStatus(403, ['msg' => 'Forbidden'], AuthenticationException::class, 'Forbidden');
    }

    public function test400IsValidationAndKeepsErrorCode(): void
    {
        $pair = ClientFactory::make();
        $pair['http']->enqueue(ClientFactory::jsonResponse(400, Fixture::json('error-400.json')));

        try {
            $pair['client']->getPaymentRequest('pr_01HZX');
            self::fail('Expected ValidationException');
        } catch (ValidationException $e) {
            self::assertSame(400, $e->statusCode);
            self::assertSame('LINE_ITEMS_TOTAL_MISMATCH', $e->errorCode);
            self::assertStringContainsString('lineItems add up', $e->getMessage());
        }
    }

    public function test422IsValidation(): void
    {
        $this->expectStatus(422, ['msg' => 'Unprocessable'], ValidationException::class, 'Unprocessable');
    }

    public function test404IsNotFound(): void
    {
        $this->expectStatus(404, Fixture::json('error-404.json'), NotFoundException::class, 'No payment request with that ID');
    }

    public function test409IsConflict(): void
    {
        $this->expectStatus(
            409,
            Fixture::json('error-409.json'),
            ConflictException::class,
            'This payment has already been paid and cannot be cancelled. Refund it instead.',
        );
    }

    public function test429ReadsRetryAfter(): void
    {
        $pair = ClientFactory::make();
        $pair['http']->enqueue(ClientFactory::jsonResponse(
            429,
            Fixture::json('error-429.json'),
            ['Retry-After' => '12'],
        ));

        try {
            $pair['client']->getPaymentRequest('pr_01HZX');
            self::fail('Expected RateLimitException');
        } catch (RateLimitException $e) {
            self::assertSame(429, $e->statusCode);
            self::assertSame(12, $e->retryAfterSeconds);
            self::assertSame('Too many requests', $e->getMessage());
        }
    }

    public function test429IgnoresHttpDateRetryAfter(): void
    {
        $pair = ClientFactory::make();
        $pair['http']->enqueue(ClientFactory::jsonResponse(
            429,
            Fixture::json('error-429.json'),
            ['Retry-After' => 'Wed, 21 Oct 2015 07:28:00 GMT'],
        ));

        try {
            $pair['client']->getPaymentRequest('pr_01HZX');
            self::fail('Expected RateLimitException');
        } catch (RateLimitException $e) {
            self::assertNull($e->retryAfterSeconds);
        }
    }

    public function test500IsServer(): void
    {
        $this->expectStatus(500, Fixture::json('error-500.json'), ServerException::class, 'Internal error');
    }

    public function testUnknownStatusFallsBackToBaseException(): void
    {
        $this->expectStatus(418, ['msg' => 'teapot'], BriizPayException::class, 'teapot');
    }

    public function testDetailFieldIsUsedWhenMsgMissing(): void
    {
        $this->expectStatus(400, ['detail' => 'Bad payload'], ValidationException::class, 'Bad payload');
    }

    public function testNonJsonErrorUsesDefaultMessage(): void
    {
        $pair = ClientFactory::make();
        $pair['http']->enqueue(new Response(500, [], 'oops'));

        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('BriizPay could not take this payment right now.');
        $pair['client']->getPaymentRequest('pr_01HZX');
    }

    public function testNetworkExceptionFromPsr18Network(): void
    {
        $pair = ClientFactory::make();
        $pair['http']->enqueue(new StubNetworkException(new Request('GET', 'https://api.briizpay.com/v1/payment-requests/x')));

        $this->expectException(NetworkException::class);
        $pair['client']->getPaymentRequest('x');
    }

    public function testNetworkExceptionFromGenericClientFailure(): void
    {
        $pair = ClientFactory::make();
        $pair['http']->enqueue(new StubClientException());

        $this->expectException(NetworkException::class);
        $pair['client']->getPaymentRequest('x');
    }

    public function testEmptySuccessPayloadIsUnexpected(): void
    {
        $pair = ClientFactory::make();
        $pair['http']->enqueue(new Response(200, [], ''));

        $this->expectException(UnexpectedResponseException::class);
        $pair['client']->getPaymentRequest('pr_01HZX');
    }

    /**
     * @param array<mixed> $body
     * @param class-string<\Throwable> $exception
     */
    private function expectStatus(int $status, array $body, string $exception, string $message): void
    {
        $pair = ClientFactory::make();
        $pair['http']->enqueue(ClientFactory::jsonResponse($status, $body));

        $this->expectException($exception);
        $this->expectExceptionMessage($message);
        $pair['client']->getPaymentRequest('pr_01HZX');
    }
}
