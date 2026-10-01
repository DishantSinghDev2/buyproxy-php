<?php

declare(strict_types=1);

namespace BuyProxy\Models;

/** Which targeting fields a plan supports. */
final class Targeting
{
    public function __construct(
        public readonly bool $country,
        public readonly bool $state,
        public readonly bool $city,
        public readonly bool $isp,
    ) {
    }

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            country: (bool) $d['country'],
            state: (bool) $d['state'],
            city: (bool) $d['city'],
            isp: (bool) $d['isp'],
        );
    }
}
