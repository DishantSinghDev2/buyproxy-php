<?php

declare(strict_types=1);

namespace BuyProxy\Models;

/** A reseller sub-account. */
final class Customer
{
    public function __construct(
        public readonly string $username,
        public readonly string $label,
        public readonly string $status,
        public readonly string $createdAt,
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
        );
    }
}
