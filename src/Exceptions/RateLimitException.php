<?php

declare(strict_types=1);

namespace BuyProxy\Exceptions;

/** 429 — more than 60 requests/minute on this key. */
class RateLimitException extends BuyProxyException
{
    public function __construct(string $message, mixed $body = null, public readonly ?float $retryAfterSeconds = null)
    {
        parent::__construct($message, 429, $body);
    }
}
