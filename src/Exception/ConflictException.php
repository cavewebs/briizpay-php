<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Exception;

/**
 * HTTP 409: a paid payment request cannot be cancelled.
 */
final class ConflictException extends BriizPayException
{
}
