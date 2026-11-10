<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Support;

use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;

final class StubNetworkException extends \RuntimeException implements NetworkExceptionInterface
{
    public function __construct(private readonly RequestInterface $request)
    {
        parent::__construct('connection refused');
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
