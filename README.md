# BriizPay PHP SDK

Typed PHP client for the BriizPay public API. Magento (`cavewebs/briizpay-magento`) and WooCommerce (`cavewebs/briizpay-woocommerce`) both talk to the same `/v1` routes; this package is the shared client.

Requires PHP 8.4 or later. HTTP is PSR-18 plus PSR-17. You supply the HTTP client.

## Install

```bash
composer require briizpay/briizpay-php
```

You also need a PSR-18 client and a PSR-17 request/stream factory. One pairing:

```bash
composer require nyholm/psr7 php-http/curl-client
```

The plugins wait 20 seconds for BriizPay. PSR-18 has no timeout of its own, so set it on the HTTP client you pass in.

## Usage

```php
use BriizPay\Sdk\Client;
use BriizPay\Sdk\Config;
use BriizPay\Sdk\Dto\CreatePaymentRequest;
use BriizPay\Sdk\Dto\CreateRefund;
use BriizPay\Sdk\Dto\Discount;
use BriizPay\Sdk\Dto\LineItem;
use BriizPay\Sdk\Dto\Money;
use Http\Client\Curl\Client as CurlClient;
use Nyholm\Psr7\Factory\Psr17Factory;

$psr17 = new Psr17Factory();
$client = new Client(
    Config::fromApiKey(getenv('BRIIZPAY_API_KEY') ?: ''),
    new CurlClient($psr17, $psr17, [CURLOPT_TIMEOUT => 20]),
    $psr17,
    $psr17,
);

$payment = $client->createPaymentRequest(new CreatePaymentRequest(
    amount: new Money(1485, 'GBP'),
    externalReference: '32',
    customerName: 'Ada Lovelace',
    customerEmail: 'ada@example.com',
    memo: 'Order 32 at Shop',
    returnUrl: 'https://shop.example/checkout/order-received/32/',
    lineItems: [
        new LineItem('T-shirt', 1, 1500, 2000, true, 'TEE-1'),
        (new Discount('Pay by bank discount', 15))->toLineItem(),
    ],
));

header('Location: ' . $payment->payUrl);
```

`Config::fromApiKey()` picks the host from the key prefix, the same way the plugins do:

- `bzp_sk_live_…` → `https://api.briizpay.com`
- `bzp_sk_sandbox_…` → `https://sandbox.api.briizpay.com`
- `bzp_sk_test_…` → `https://dev.api.briizpay.com`

Pass a `Config` yourself when you already know the base URL.

### Endpoints

| Method | Path | Client method |
| --- | --- | --- |
| POST | `/v1/payment-requests` | `createPaymentRequest()` |
| GET | `/v1/payment-requests/{id}` | `getPaymentRequest()` |
| POST | `/v1/payment-requests/{id}/cancel` | `cancelPaymentRequest()` |
| POST | `/v1/refunds` | `createRefund()` |

Amounts are integer minor units plus a 3-letter currency. Receipt lines must add up to `amountMinor` or BriizPay returns `LINE_ITEMS_TOTAL_MISMATCH`. A shop discount is a negative line (`Discount` builds that line for you).

`createRefund()` reports a shop refund. It does not move money. Set `externalReference` to the shop's own refund id so a retry returns the first row. The response keeps `mode` (`auto` or `manual`) and `msg` from the envelope so you can tell the merchant whether they still have to send the money. The plugins unwrap `data` and drop those fields; this SDK does not.

### Webhooks

Header: `Briizpay-Signature: t=<unix>,v1=<hex>`. The MAC is HMAC-SHA256 of `timestamp.body`, 300 second skew.

```php
$payload = file_get_contents('php://input') ?: '';
$header = $_SERVER['HTTP_BRIIZPAY_SIGNATURE'] ?? null;
$event = $client->parse($payload, $header, getenv('BRIIZPAY_WEBHOOK_SECRET') ?: '');

if ($event->isPaymentRequestPaid()) {
    $orderId = $event->data->externalReference;
}
```

Delivery is at-least-once. Ignore an event `id` you have already applied.

### Injecting and mocking

`BriizPay\Sdk\ClientInterface` extends:

- `Contract\PaymentRequestsInterface`
- `Contract\RefundsInterface`
- `Contract\WebhookVerifierInterface`

Plugins should type-hint those interfaces.

### Errors

| HTTP | Exception |
| --- | --- |
| 401, 403 | `AuthenticationException` |
| 400, 422 | `ValidationException` |
| 404 | `NotFoundException` |
| 409 | `ConflictException` (cancel after the payment is already paid) |
| 429 | `RateLimitException` (`retryAfterSeconds` when `Retry-After` is a delay) |
| 5xx | `ServerException` |
| transport failure | `NetworkException` |

All of those extend `BriizPayException` (`statusCode`, `errorCode`).

## Development

```bash
composer install
composer test
composer phpstan
composer cs-check
```

PHP 8.4 is the minimum. GitHub Actions runs the same three jobs on 8.4 and 8.5.

## Licence

Dual-licensed OSL-3.0 and GPL-2.0-or-later, matching the Magento module and the WooCommerce plugin. Use either licence.
