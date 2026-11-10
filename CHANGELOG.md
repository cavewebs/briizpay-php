# Changelog

## [Unreleased]

## [0.1.0] - 2026-11-10

### Added

- First cut of `briizpay/briizpay-php`: PSR-18/PSR-17 client, typed DTOs, webhook HMAC check.
- Payment requests: create, get, cancel.
- Refunds: create, including envelope `mode` and `msg`.
- Webhooks: `payment_request.paid` parse and `Briizpay-Signature` verify.
- Nullable `transactionId` via `getTransactionId(): ?string` on `PaymentRequest`, `WebhookPaymentData`, and `Refund`. JSON `null` and a missing key both parse as `null`.
- Typed errors for 401/403, 400/422, 404, 409, 429, 5xx, and transport failures.
- PHPUnit, PHPStan max, PHP-CS-Fixer PSR-12, GitHub Actions on PHP 8.4 and 8.5.
