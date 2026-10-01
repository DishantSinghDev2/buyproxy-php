<?php

declare(strict_types=1);

namespace BuyProxy\Models;

/** Unallocated reseller bandwidth on one product. */
final class PoolEntry
{
    public function __construct(
        public readonly string $productKey,
        public readonly string $productId,
        public readonly string $label,
        public readonly float $gb,
    ) {
    }

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            productKey: (string) $d['productKey'],
            productId: (string) $d['productId'],
            label: (string) $d['label'],
            gb: (float) $d['gb'],
        );
    }
}
