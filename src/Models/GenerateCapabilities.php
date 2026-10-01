<?php

declare(strict_types=1);

namespace BuyProxy\Models;

/** What a plan's generate call accepts. */
final class GenerateCapabilities
{
    /**
     * @param list<string> $protocols
     */
    public function __construct(
        public readonly int $maxCount,
        public readonly int $maxStickyMinutes,
        public readonly array $protocols,
        public readonly Targeting $targeting,
    ) {
    }

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            maxCount: (int) $d['maxCount'],
            maxStickyMinutes: (int) $d['maxStickyMinutes'],
            protocols: $d['protocols'] ?? [],
            targeting: Targeting::fromArray($d['targeting']),
        );
    }
}
