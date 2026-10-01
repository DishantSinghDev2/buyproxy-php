<?php

declare(strict_types=1);

namespace BuyProxy\Models;

/** One payment. */
final class Transaction
{
    public function __construct(
        public readonly string $id,
        public readonly string $kind,
        public readonly string $status,
        public readonly string $paymentMethod,
        public readonly ?string $interval,
        public readonly int $amountCents,
        public readonly string $currency,
        public readonly ?float $gb,
        public readonly ?string $productKey,
        public readonly ?string $plan,
        public readonly bool $resellerPool,
        public readonly string $createdAt,
    ) {
    }

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            id: (string) $d['id'],
            kind: (string) $d['kind'],
            status: (string) $d['status'],
            paymentMethod: (string) $d['paymentMethod'],
            interval: isset($d['interval']) ? (string) $d['interval'] : null,
            amountCents: (int) $d['amountCents'],
            currency: (string) $d['currency'],
            gb: isset($d['gb']) ? (float) $d['gb'] : null,
            productKey: isset($d['productKey']) ? (string) $d['productKey'] : null,
            plan: isset($d['plan']) ? (string) $d['plan'] : null,
            resellerPool: (bool) $d['resellerPool'],
            createdAt: (string) $d['createdAt'],
        );
    }
}
