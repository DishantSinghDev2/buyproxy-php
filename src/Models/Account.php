<?php

declare(strict_types=1);

namespace BuyProxy\Models;

/** The account behind the API key. */
final class Account
{
    public function __construct(
        public readonly string $email,
        public readonly string $name,
        public readonly string $plan,
        public readonly ?string $planExpiresAt,
        public readonly float $balanceGb,
        public readonly string $createdAt,
    ) {
    }

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            email: (string) $d['email'],
            name: (string) $d['name'],
            plan: (string) $d['plan'],
            planExpiresAt: isset($d['planExpiresAt']) ? (string) $d['planExpiresAt'] : null,
            balanceGb: (float) $d['balanceGb'],
            createdAt: (string) $d['createdAt'],
        );
    }
}
