<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Contract;

use BriizPay\Sdk\Dto\CreateRefund;
use BriizPay\Sdk\Dto\Refund;

interface RefundsInterface
{
    public function createRefund(CreateRefund $request): Refund;
}
