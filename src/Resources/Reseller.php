<?php

declare(strict_types=1);

namespace BuyProxy\Resources;

use BuyProxy\Client;
use BuyProxy\Models\ResellerSummary;

/** Reseller API — needs a `bp_live_` key on a Business plan or higher. */
final class Reseller extends Resource
{
    public readonly Customers $customers;
    public readonly Pool $pool;

    public function __construct(Client $client)
    {
        parent::__construct($client);
        $this->customers = new Customers($client);
        $this->pool = new Pool($client);
    }

    public function me(): ResellerSummary
    {
        return ResellerSummary::fromArray($this->client->request('GET', '/v1/me', retry5xx: true));
    }
}
