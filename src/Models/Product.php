<?php

declare(strict_types=1);

namespace BuyProxy\Models;

/** A product in the catalog. */
final class Product
{
    public function __construct(
        public readonly string $productKey,
        public readonly string $productId,
        public readonly string $name,
        public readonly string $description,
        public readonly int $minGb,
        public readonly ?int $pricePerGbCents,
        public readonly string $currency,
    ) {
    }

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            productKey: (string) $d['productKey'],
            productId: (string) $d['productId'],
            name: (string) $d['name'],
            description: (string) $d['description'],
            minGb: (int) $d['minGb'],
            pricePerGbCents: isset($d['pricePerGbCents']) ? (int) $d['pricePerGbCents'] : null,
            currency: (string) $d['currency'],
        );
    }
}
