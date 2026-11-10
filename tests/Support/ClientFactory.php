<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests\Support;

use BriizPay\Sdk\Client;
use BriizPay\Sdk\Config;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;

final class ClientFactory
{
    public static function liveKey(): string
    {
        return 'bzp_sk_live_' . str_repeat('a', 40);
    }

    /**
     * @return array{client: Client, http: MockHttpClient}
     */
    public static function make(?Config $config = null): array
    {
        $http = new MockHttpClient();
        $psr17 = new Psr17Factory();
        $client = new Client(
            $config ?? Config::fromApiKey(self::liveKey()),
            $http,
            $psr17,
            $psr17,
        );

        return ['client' => $client, 'http' => $http];
    }

    /**
     * @param array<mixed> $body
     * @param array<string, string> $headers
     */
    public static function jsonResponse(int $status, array $body, array $headers = []): Response
    {
        $encoded = json_encode($body, JSON_THROW_ON_ERROR);

        return new Response($status, array_merge(['Content-Type' => 'application/json'], $headers), $encoded);
    }
}
