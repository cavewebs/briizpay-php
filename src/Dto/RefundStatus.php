<?php

declare(strict_types=1);

namespace BriizPay\Sdk\Dto;

enum RefundStatus: string
{
    case Pending = 'PENDING';
    case Completed = 'COMPLETED';
    case Failed = 'FAILED';
    case Held = 'HELD';

    public function isSettled(): bool
    {
        return $this === self::Completed;
    }

    public static function fromApi(string $status): self
    {
        $known = self::tryFrom($status);
        if ($known instanceof self) {
            return $known;
        }

        throw new \InvalidArgumentException(sprintf('Unknown refund status "%s".', $status));
    }
}
