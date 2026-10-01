<?php

declare(strict_types=1);

namespace BuyProxy\Resources;

use BuyProxy\Client;

/** @internal */
abstract class Resource
{
    public function __construct(protected readonly Client $client)
    {
    }

    /**
     * Drop unset (null) fields so defaults are decided by the API, not the SDK.
     *
     * @param array<string, mixed> $fields
     * @return array<string, mixed>
     */
    protected static function compact(array $fields): array
    {
        return array_filter($fields, static fn ($v): bool => $v !== null);
    }

    protected static function seg(string $value): string
    {
        return rawurlencode($value);
    }
}
