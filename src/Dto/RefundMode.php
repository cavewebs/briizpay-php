<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Dto;

/**
 * Whether BriizPay moved the money (auto) or the merchant still has to send it (manual).
 */
enum RefundMode: string
{
    case Auto = 'auto';
    case Manual = 'manual';
}
