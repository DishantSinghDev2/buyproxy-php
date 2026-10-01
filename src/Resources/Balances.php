<?php

declare(strict_types=1);

namespace BuyProxy\Resources;

use BuyProxy\Models\Balance;

final class Balances extends Resource
{
    /**
     * Spendable GB per product, including GB still being added after a payment.
     *
     * @return list<Balance>
     */
    public function list(): array
    {
        $r = $this->client->request('GET', '/v1/balances', retry5xx: true);

        return array_map(static fn (array $b): Balance => Balance::fromArray($b), $r['balances']);
    }
}
