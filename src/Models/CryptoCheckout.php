<?php

declare(strict_types=1);

namespace BuyProxy\Models;

/** A crypto invoice to open in a browser. */
final class CryptoCheckout
{
    public function __construct(
        public readonly string $url,
        public readonly string $transactionId,
    ) {
    }

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            url: (string) $d['url'],
            transactionId: (string) $d['transactionId'],
        );
    }
}
