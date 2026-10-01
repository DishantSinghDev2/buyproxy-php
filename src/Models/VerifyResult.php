<?php

declare(strict_types=1);

namespace BuyProxy\Models;

/** The result of OTP verification. */
final class VerifyResult
{
    public function __construct(
        public readonly bool $ok,
        public readonly bool $isNew,
        public readonly VerifiedUser $user,
    ) {
    }

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            ok: (bool) $d['ok'],
            isNew: (bool) $d['isNew'],
            user: VerifiedUser::fromArray($d['user']),
        );
    }
}
