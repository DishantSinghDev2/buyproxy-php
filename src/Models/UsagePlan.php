<?php

declare(strict_types=1);

namespace BuyProxy\Models;

/** Consumption on one plan. */
final class UsagePlan
{
    public function __construct(
        public readonly string $id,
        public readonly string $productId,
        public readonly ?string $productKey,
        public readonly float $remainingGb,
        public readonly float $totalGb,
        public readonly float $usedGb,
        public readonly ?string $expiresAt,
    ) {
    }

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            id: (string) $d['id'],
            productId: (string) $d['productId'],
            productKey: isset($d['productKey']) ? (string) $d['productKey'] : null,
            remainingGb: (float) $d['remainingGb'],
            totalGb: (float) $d['totalGb'],
            usedGb: (float) $d['usedGb'],
            expiresAt: isset($d['expiresAt']) ? (string) $d['expiresAt'] : null,
        );
    }
}
