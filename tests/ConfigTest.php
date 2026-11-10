<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Tests;

use BriizPay\Sdk\Config;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testFromApiKeyMapsHostsLikeThePlugins(): void
    {
        $live = Config::fromApiKey('bzp_sk_live_' . str_repeat('a', 40));
        self::assertSame(Config::LIVE_BASE_URL, $live->baseUrl);
        self::assertSame('live', $live->environment());

        $sandbox = Config::fromApiKey('bzp_sk_sandbox_' . str_repeat('b', 40));
        self::assertSame(Config::SANDBOX_BASE_URL, $sandbox->baseUrl);
        self::assertSame('sandbox', $sandbox->environment());

        $test = Config::fromApiKey('bzp_sk_test_' . str_repeat('c', 40));
        self::assertSame(Config::TEST_BASE_URL, $test->baseUrl);
        self::assertSame('test', $test->environment());
    }

    public function testTrimsAndStripsTrailingSlash(): void
    {
        $config = new Config('  key  ', 'https://api.briizpay.com/');

        self::assertSame('key', $config->apiKey);
        self::assertSame('https://api.briizpay.com', $config->baseUrl);
    }

    public function testRejectsEmptyKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Config(' ', 'https://api.briizpay.com');
    }

    public function testRejectsNonHttpBaseUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Config('key', 'ftp://api.briizpay.com');
    }
}
