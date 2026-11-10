<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Support;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class MockHttpClient implements ClientInterface
{
    /** @var list<ResponseInterface|\Throwable> */
    private array $queue = [];

    /** @var list<RequestInterface> */
    public array $requests = [];

    public function enqueue(ResponseInterface|\Throwable $next): void
    {
        $this->queue[] = $next;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;
        $next = array_shift($this->queue);
        if ($next instanceof \Throwable) {
            throw $next;
        }

        if ($next instanceof ResponseInterface) {
            return $next;
        }

        throw new \RuntimeException('No queued HTTP response.');
    }
}
