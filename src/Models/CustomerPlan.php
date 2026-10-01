<?php

declare(strict_types=1);

namespace BuyProxy\Models;

/** A plan plus the customer's remaining GB. */
final class CustomerPlan
{
    public function __construct(
        public readonly string $id,
        public readonly string $productId,
        public readonly ?string $productKey,
        public readonly string $name,
        public readonly ?string $label,
        public readonly float $dataGbRemaining,
        public readonly float $dataGbTotal,
        public readonly ?string $expiresAt,
        public readonly string $createdAt,
        public readonly ?GenerateCapabilities $generate,
        public readonly float $remainingGb,
    ) {
    }

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            id: (string) $d['id'],
            productId: (string) $d['productId'],
            productKey: isset($d['productKey']) ? (string) $d['productKey'] : null,
            name: (string) $d['name'],
            label: isset($d['label']) ? (string) $d['label'] : null,
            dataGbRemaining: (float) $d['dataGbRemaining'],
            dataGbTotal: (float) $d['dataGbTotal'],
            expiresAt: isset($d['expiresAt']) ? (string) $d['expiresAt'] : null,
            createdAt: (string) $d['createdAt'],
            generate: isset($d['generate']) ? GenerateCapabilities::fromArray($d['generate']) : null,
            remainingGb: (float) $d['remainingGb'],
        );
    }
}
