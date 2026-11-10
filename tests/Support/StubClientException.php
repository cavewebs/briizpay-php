<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Support;

use Psr\Http\Client\ClientExceptionInterface;

final class StubClientException extends \RuntimeException implements ClientExceptionInterface
{
    public function __construct()
    {
        parent::__construct('HTTP client failed');
    }
}
