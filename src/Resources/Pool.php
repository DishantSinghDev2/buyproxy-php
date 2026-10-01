<?php

declare(strict_types=1);

namespace BuyProxy\Resources;

use BuyProxy\Models\CardCheckout;
use BuyProxy\Models\CryptoCheckout;
use BuyProxy\Models\PoolEntry;

final class Pool extends Resource
{
    /**
     * Your unallocated bandwidth, per product.
     *
     * @return list<PoolEntry>
     */
    public function get(): array
    {
        $r = $this->client->request('GET', '/v1/pool', retry5xx: true);

        return array_map(static fn (array $p): PoolEntry => PoolEntry::fromArray($p), $r['pool']);
    }

    public function topupCard(string $productKey, int $gb): CardCheckout
    {
        return CardCheckout::fromArray($this->client->request('POST', '/v1/pool/topup/card', body: ['productKey' => $productKey, 'gb' => $gb]));
    }

    public function topupCrypto(string $productKey, int $gb): CryptoCheckout
    {
        return CryptoCheckout::fromArray($this->client->request('POST', '/v1/pool/topup/crypto', body: ['productKey' => $productKey, 'gb' => $gb]));
    }
}
