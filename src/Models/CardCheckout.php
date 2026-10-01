<?php

declare(strict_types=1);

namespace BuyProxy\Models;

/** A Stripe checkout to open in a browser. */
final class CardCheckout
{
    public function __construct(
        public readonly string $payUrl,
        public readonly ?string $transactionId,
    ) {
    }

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            payUrl: (string) $d['payUrl'],
            transactionId: isset($d['transactionId']) ? (string) $d['transactionId'] : null,
        );
    }
}
