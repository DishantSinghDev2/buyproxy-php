<?php

declare(strict_types=1);

namespace BuyProxy\Models;

/** The verified user. */
final class VerifiedUser
{
    public function __construct(
        public readonly string $email,
        public readonly string $name,
    ) {
    }

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            email: (string) $d['email'],
            name: (string) $d['name'],
        );
    }
}
