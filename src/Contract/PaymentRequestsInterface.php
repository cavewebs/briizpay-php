<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Contract;

use BriizPay\Sdk\Dto\CreatePaymentRequest;
use BriizPay\Sdk\Dto\PaymentRequest;

interface PaymentRequestsInterface
{
    public function createPaymentRequest(CreatePaymentRequest $request): PaymentRequest;

    public function getPaymentRequest(string $id): PaymentRequest;

    public function cancelPaymentRequest(string $id): PaymentRequest;
}
