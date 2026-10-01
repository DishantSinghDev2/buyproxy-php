<?php

declare(strict_types=1);

namespace BuyProxy\Models;

/** Reseller account summary. */
final class ResellerSummary
{
    /**
     * @param list<PoolEntry> $pool
     */
    public function __construct(
        public readonly string $email,
        public readonly int $customerCount,
        public readonly int $activeCustomerCount,
        public readonly array $pool,
    ) {
    }

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            email: (string) $d['email'],
            customerCount: (int) $d['customerCount'],
            activeCustomerCount: (int) $d['activeCustomerCount'],
            pool: array_map(static fn (array $x): PoolEntry => PoolEntry::fromArray($x), $d['pool'] ?? []),
        );
    }
}
