<?php

declare(strict_types=1);

namespace BuyProxy\Models;

/** Top-ups of thresholdGb or more get floor(gb × rate) extra GB free. */
final class VolumeBonus
{
    public function __construct(
        public readonly int $thresholdGb,
        public readonly float $rate,
    ) {
    }

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            thresholdGb: (int) $d['thresholdGb'],
            rate: (float) $d['rate'],
        );
    }
}
