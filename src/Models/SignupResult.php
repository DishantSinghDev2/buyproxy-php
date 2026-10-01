<?php

declare(strict_types=1);

namespace BuyProxy\Models;

/** The result of signup. */
final class SignupResult
{
    public function __construct(
        public readonly bool $ok,
        public readonly ?int $cooldownSeconds,
    ) {
    }

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            ok: (bool) $d['ok'],
            cooldownSeconds: isset($d['cooldownSeconds']) ? (int) $d['cooldownSeconds'] : null,
        );
    }
}
