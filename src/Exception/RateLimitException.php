<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Exception;

final class RateLimitException extends BriizPayException
{
    public function __construct(
        string $message,
        int $statusCode = 429,
        ?string $errorCode = null,
        public readonly ?int $retryAfterSeconds = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $errorCode, $previous);
    }
}
