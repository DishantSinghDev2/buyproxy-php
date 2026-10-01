<?php

declare(strict_types=1);

namespace BuyProxy\Resources;

use BuyProxy\Models\Catalog;

final class Products extends Resource
{
    /** Every product with its `productKey`, minimum GB and live price. No API key needed. */
    public function list(): Catalog
    {
        return Catalog::fromArray($this->client->request('GET', '/v1/products', public: true, retry5xx: true));
    }
}
