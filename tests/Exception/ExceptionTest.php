<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Exception;

use BriizPay\Sdk\Exception\RateLimitException;
use BriizPay\Sdk\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

final class ExceptionTest extends TestCase
{
    public function testStatusAndErrorCodeAreReadable(): void
    {
        $e = new ValidationException('bad', 400, 'LINE_ITEMS_TOTAL_MISMATCH');

        self::assertSame(400, $e->statusCode);
        self::assertSame('LINE_ITEMS_TOTAL_MISMATCH', $e->errorCode);
        self::assertSame(400, $e->getCode());
    }

    public function testRateLimitKeepsRetryAfter(): void
    {
        $e = new RateLimitException('slow', 429, null, 5);

        self::assertSame(5, $e->retryAfterSeconds);
        self::assertSame(429, $e->statusCode);
    }
}
