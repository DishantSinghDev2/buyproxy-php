<?php

declare(strict_types=1);

namespace BuyProxy\Resources;

use BuyProxy\Exceptions\BuyProxyException;
use BuyProxy\Models\Transaction;

final class Transactions extends Resource
{
    /** @return list<Transaction> */
    public function list(?int $limit = null): array
    {
        $r = $this->client->request('GET', '/v1/transactions', query: ['limit' => $limit], retry5xx: true);

        return array_map(static fn (array $t): Transaction => Transaction::fromArray($t), $r['transactions']);
    }

    public function get(string $id): Transaction
    {
        return Transaction::fromArray($this->client->request('GET', '/v1/transactions/' . self::seg($id), retry5xx: true));
    }

    /** Poll until the payment settles (`succeeded` or `failed`). */
    public function wait(string $id, float $timeout = 600.0, float $interval = 3.0): Transaction
    {
        $deadline = microtime(true) + $timeout;
        while (true) {
            $tx = $this->get($id);
            if ($tx->status !== 'pending') {
                return $tx;
            }
            if (microtime(true) + $interval > $deadline) {
                throw new BuyProxyException("Payment $id is still pending after waiting");
            }
            usleep((int) ($interval * 1_000_000));
        }
    }
}
