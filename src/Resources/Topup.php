<?php

declare(strict_types=1);

namespace BuyProxy\Resources;

use BuyProxy\Models\CardCheckout;
use BuyProxy\Models\CryptoCheckout;

final class Topup extends Resource
{
    /**
     * Card checkout via Stripe: `card(productKey: 'residential', gb: 10)` or `card(plan: 'pro', interval: 'year')`.
     * Open `payUrl` to pay.
     */
    public function card(?string $productKey = null, ?int $gb = null, ?string $plan = null, ?string $interval = null): CardCheckout
    {
        if ($plan !== null) {
            $body = self::compact(['kind' => 'plan', 'plan' => $plan, 'interval' => $interval]);
        } elseif ($productKey !== null && $gb !== null) {
            $body = ['kind' => 'topup', 'productKey' => $productKey, 'gb' => $gb];
        } else {
            throw new \InvalidArgumentException('Pass either productKey and gb (a top-up) or plan (a subscription).');
        }

        return CardCheckout::fromArray($this->client->request('POST', '/v1/topup/card', body: $body));
    }

    /** Crypto top-up (pay-as-you-go only). Open `url` to pay. */
    public function crypto(string $productKey, int $gb): CryptoCheckout
    {
        return CryptoCheckout::fromArray($this->client->request('POST', '/v1/topup/crypto', body: ['kind' => 'topup', 'productKey' => $productKey, 'gb' => $gb]));
    }
}
