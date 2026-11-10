<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Dto;

enum PaymentStatus: string
{
    case Pending = 'PENDING';
    case Cancelled = 'CANCELLED';
    case Failed = 'FAILED';
    case Completed = 'COMPLETED';
    case Expired = 'EXPIRED';

    public function isPaid(): bool
    {
        return $this === self::Completed;
    }

    public function isTerminalUnpaid(): bool
    {
        return $this === self::Cancelled || $this === self::Expired || $this === self::Failed;
    }

    public static function fromApi(string $status): self
    {
        $known = self::tryFrom($status);
        if ($known instanceof self) {
            return $known;
        }

        throw new \InvalidArgumentException(sprintf('Unknown payment status "%s".', $status));
    }
}
