# Changelog

## [Unreleased]

### Added

- First cut of `briizpay/briizpay-php`: PSR-18/PSR-17 client, typed DTOs, webhook HMAC check.
- Payment requests: create, get, cancel.
- Refunds: create, including envelope `mode` and `msg`.
- Webhooks: `payment_request.paid` parse and `Briizpay-Signature` verify.
- Typed errors for 401/403, 400/422, 404, 409, 429, 5xx, and transport failures.
- PHPUnit, PHPStan max, PHP-CS-Fixer PSR-12, GitHub Actions on PHP 8.4 and 8.5.
