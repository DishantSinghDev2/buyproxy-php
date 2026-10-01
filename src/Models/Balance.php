<?php

declare(strict_types=1);

namespace BuyProxy\Models;

/** Spendable bandwidth on one product. */
final class Balance
{
    public function __construct(
        public readonly ?string $productKey,
        public readonly string $productId,
        public readonly ?string $planId,
        public readonly float $activeGb,
        public readonly float $pendingGb,
        public readonly float $totalGb,
        public readonly ?string $expiresAt,
    ) {
    }

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            productKey: isset($d['productKey']) ? (string) $d['productKey'] : null,
            productId: (string) $d['productId'],
            planId: isset($d['planId']) ? (string) $d['planId'] : null,
            activeGb: (float) $d['activeGb'],
            pendingGb: (float) $d['pendingGb'],
            totalGb: (float) $d['totalGb'],
            expiresAt: isset($d['expiresAt']) ? (string) $d['expiresAt'] : null,
        );
    }
}
