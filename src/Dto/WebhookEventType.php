<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Dto;

enum WebhookEventType: string
{
    case PaymentRequestPaid = 'payment_request.paid';
}
