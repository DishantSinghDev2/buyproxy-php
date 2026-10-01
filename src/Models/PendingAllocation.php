<?php

declare(strict_types=1);

namespace BuyProxy\Models;

/** GB allocated but still being added to a plan. */
final class PendingAllocation
{
    public function __construct(
        public readonly string $productId,
        public readonly ?string $productKey,
        public readonly float $gb,
    ) {
    }

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            productId: (string) $d['productId'],
            productKey: isset($d['productKey']) ? (string) $d['productKey'] : null,
            gb: (float) $d['gb'],
        );
    }
}
