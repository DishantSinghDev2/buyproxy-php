<?php

declare(strict_types=1);

namespace BuyProxy\Models;

/** A subscription plan's price. */
final class PlanPrice
{
    public function __construct(
        public readonly string $plan,
        public readonly int $monthlyPriceCents,
        public readonly float $includedGb,
    ) {
    }

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            plan: (string) $d['plan'],
            monthlyPriceCents: (int) $d['monthlyPriceCents'],
            includedGb: (float) $d['includedGb'],
        );
    }
}
