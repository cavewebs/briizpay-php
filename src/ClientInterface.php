<?php

declare(strict_types=1);

namespace BriizPay\Sdk;

use BriizPay\Sdk\Contract\PaymentRequestsInterface;
use BriizPay\Sdk\Contract\RefundsInterface;
use BriizPay\Sdk\Contract\WebhookVerifierInterface;

/**
 * The surface Magento and WooCommerce call today, behind one injectable type.
 */
interface ClientInterface extends PaymentRequestsInterface, RefundsInterface, WebhookVerifierInterface
{
}
