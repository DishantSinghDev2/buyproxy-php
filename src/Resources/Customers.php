<?php

declare(strict_types=1);

namespace BuyProxy\Resources;

use BuyProxy\GeneratedProxies;
use BuyProxy\Models\Allocation;
use BuyProxy\Models\Customer;
use BuyProxy\Models\CustomerDetail;
use BuyProxy\Models\CustomerUsage;

final class Customers extends Resource
{
    /** @return list<Customer> */
    public function list(?string $status = null): array
    {
        $r = $this->client->request('GET', '/v1/customers', query: ['status' => $status], retry5xx: true);

        return array_map(static fn (array $c): Customer => Customer::fromArray($c), $r['customers']);
    }

    /** Creates an isolated sub-account with no bandwidth — fund it with {@see allocate()}. */
    public function create(string $label): Customer
    {
        return Customer::fromArray($this->client->request('POST', '/v1/customers', body: ['label' => $label]));
    }

    public function get(string $username): CustomerDetail
    {
        return CustomerDetail::fromArray($this->client->request('GET', '/v1/customers/' . self::seg($username), retry5xx: true));
    }

    /** Rename and/or suspend (`'suspended'`) / reactivate (`'active'`). */
    public function update(string $username, ?string $label = null, ?string $status = null): Customer
    {
        return Customer::fromArray($this->client->request('PATCH', '/v1/customers/' . self::seg($username), body: self::compact(['label' => $label, 'status' => $status])));
    }

    public function usage(string $username): CustomerUsage
    {
        return CustomerUsage::fromArray($this->client->request('GET', '/v1/customers/' . self::seg($username) . '/usage', retry5xx: true));
    }

    /** Move GB from your pool onto a customer. Pass `idempotencyKey` to make retries safe. */
    public function allocate(string $username, string $productKey, int $gb, ?string $idempotencyKey = null): Allocation
    {
        $r = $this->client->request(
            'POST',
            '/v1/customers/' . self::seg($username) . '/allocate',
            body: ['productKey' => $productKey, 'gb' => $gb],
            headers: $idempotencyKey !== null ? ['Idempotency-Key' => $idempotencyKey] : [],
            retry5xx: $idempotencyKey !== null,
        );

        return Allocation::fromArray($r);
    }

    public function generate(
        string $username,
        string $planId,
        ?string $protocol = null,
        ?int $count = null,
        ?string $country = null,
        ?string $state = null,
        ?string $city = null,
        ?string $isp = null,
        ?int $stickyMinutes = null,
    ): GeneratedProxies {
        $body = self::compact(['planId' => $planId] + compact('protocol', 'count', 'country', 'state', 'city', 'isp', 'stickyMinutes'));
        $r = $this->client->request('POST', '/v1/customers/' . self::seg($username) . '/generate', body: $body);

        return GeneratedProxies::fromLines($r['lines'], $protocol ?? 'http');
    }
}
