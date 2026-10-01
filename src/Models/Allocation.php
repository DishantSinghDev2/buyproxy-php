<?php

declare(strict_types=1);

namespace BuyProxy\Models;

/** The result of an allocation. */
final class Allocation
{
    public function __construct(
        public readonly string $productKey,
        public readonly string $productId,
        public readonly int $gb,
        public readonly bool $alreadyApplied,
        public readonly float $poolRemainingGb,
    ) {
    }

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            productKey: (string) $d['productKey'],
            productId: (string) $d['productId'],
            gb: (int) $d['gb'],
            alreadyApplied: (bool) $d['alreadyApplied'],
            poolRemainingGb: (float) $d['poolRemainingGb'],
        );
    }
}
