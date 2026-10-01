<?php

declare(strict_types=1);

namespace BuyProxy\Models;

/** A snapshot of a customer's consumption. */
final class CustomerUsage
{
    /**
     * @param list<UsagePlan> $plans
     * @param list<PendingAllocation> $pending
     */
    public function __construct(
        public readonly array $plans,
        public readonly array $pending,
    ) {
    }

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            plans: array_map(static fn (array $x): UsagePlan => UsagePlan::fromArray($x), $d['plans'] ?? []),
            pending: array_map(static fn (array $x): PendingAllocation => PendingAllocation::fromArray($x), $d['pending'] ?? []),
        );
    }
}
