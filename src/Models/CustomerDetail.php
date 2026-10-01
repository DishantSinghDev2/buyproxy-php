<?php

declare(strict_types=1);

namespace BuyProxy\Models;

/** A customer with their plans. */
final class CustomerDetail
{
    /**
     * @param list<CustomerPlan> $plans
     * @param list<PendingAllocation> $pending
     */
    public function __construct(
        public readonly string $username,
        public readonly string $label,
        public readonly string $status,
        public readonly string $createdAt,
        public readonly array $plans,
        public readonly array $pending,
    ) {
    }

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            username: (string) $d['username'],
            label: (string) $d['label'],
            status: (string) $d['status'],
            createdAt: (string) $d['createdAt'],
            plans: array_map(static fn (array $x): CustomerPlan => CustomerPlan::fromArray($x), $d['plans'] ?? []),
            pending: array_map(static fn (array $x): PendingAllocation => PendingAllocation::fromArray($x), $d['pending'] ?? []),
        );
    }
}
