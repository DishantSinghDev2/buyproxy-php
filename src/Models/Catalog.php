<?php

declare(strict_types=1);

namespace BuyProxy\Models;

/** The public product catalog with live prices. */
final class Catalog
{
    /**
     * @param list<Product> $products
     * @param list<PlanPrice> $plans
     */
    public function __construct(
        public readonly array $products,
        public readonly VolumeBonus $volumeBonus,
        public readonly array $plans,
    ) {
    }

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            products: array_map(static fn (array $x): Product => Product::fromArray($x), $d['products'] ?? []),
            volumeBonus: VolumeBonus::fromArray($d['volumeBonus']),
            plans: array_map(static fn (array $x): PlanPrice => PlanPrice::fromArray($x), $d['plans'] ?? []),
        );
    }
}
