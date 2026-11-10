<?php

declare(strict_types=1);

namespace BriizPay\Sdk;

use SensitiveParameter;

/**
 * API key and host for one BriizPay environment.
 *
 * The plugins derive the host from the key prefix. This class can do the same,
 * or you can pass a base URL yourself when you already know the environment.
 */
final readonly class Config
{
    public const LIVE_BASE_URL = 'https://api.briizpay.com';

    public const SANDBOX_BASE_URL = 'https://sandbox.api.briizpay.com';

    public const TEST_BASE_URL = 'https://dev.api.briizpay.com';

    public string $apiKey;

    public string $baseUrl;

    public function __construct(
        #[SensitiveParameter]
        string $apiKey,
        string $baseUrl,
    ) {
        $key = trim($apiKey);
        if ($key === '') {
            throw new \InvalidArgumentException('API key must not be empty.');
        }

        $base = rtrim(trim($baseUrl), '/');
        if ($base === '' || filter_var($base, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException('Base URL must be an absolute HTTP(S) URL.');
        }

        if (!str_starts_with($base, 'https://') && !str_starts_with($base, 'http://')) {
            throw new \InvalidArgumentException('Base URL must be HTTP or HTTPS.');
        }

        $this->apiKey = $key;
        $this->baseUrl = $base;
    }

    /**
     * Host from the key prefix, the same mapping as the Magento and WooCommerce clients.
     */
    public static function fromApiKey(#[SensitiveParameter] string $apiKey): self
    {
        $key = trim($apiKey);
        $base = match (true) {
            str_starts_with($key, 'bzp_sk_sandbox_') => self::SANDBOX_BASE_URL,
            str_starts_with($key, 'bzp_sk_test_') => self::TEST_BASE_URL,
            default => self::LIVE_BASE_URL,
        };

        return new self($key, $base);
    }

    public function environment(): string
    {
        if (str_starts_with($this->apiKey, 'bzp_sk_sandbox_')) {
            return 'sandbox';
        }

        if (str_starts_with($this->apiKey, 'bzp_sk_test_')) {
            return 'test';
        }

        return 'live';
    }
}
