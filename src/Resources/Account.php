<?php

declare(strict_types=1);

namespace BuyProxy\Resources;

use BuyProxy\Models\Account as AccountModel;

final class Account extends Resource
{
    public function get(): AccountModel
    {
        return AccountModel::fromArray($this->client->request('GET', '/v1/account', retry5xx: true));
    }
}
