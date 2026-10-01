<?php

declare(strict_types=1);

namespace BuyProxy\Resources;

use BuyProxy\GeneratedProxies;
use BuyProxy\Models\Plan;

final class Plans extends Resource
{
    /** @return list<Plan> */
    public function list(): array
    {
        $r = $this->client->request('GET', '/v1/plans', retry5xx: true);

        return array_map(static fn (array $p): Plan => Plan::fromArray($p), $r['plans']);
    }

    /**
     * Generate proxy lines against one of your plans. Lines are on-demand — don't store them.
     * Omit `stickyMinutes` for a fresh IP per request (rotating).
     */
    public function generate(
        string $planId,
        ?string $protocol = null,
        ?int $count = null,
        ?string $country = null,
        ?string $state = null,
        ?string $city = null,
        ?string $isp = null,
        ?int $stickyMinutes = null,
    ): GeneratedProxies {
        $body = self::compact(compact('protocol', 'count', 'country', 'state', 'city', 'isp', 'stickyMinutes'));
        $r = $this->client->request('POST', '/v1/plans/' . self::seg($planId) . '/generate', body: $body);

        return GeneratedProxies::fromLines($r['lines'], $protocol ?? 'http');
    }
}
