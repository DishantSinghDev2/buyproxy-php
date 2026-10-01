<?php

declare(strict_types=1);

namespace BuyProxy;

use BuyProxy\Exceptions\AuthenticationException;
use BuyProxy\Exceptions\BuyProxyException;
use BuyProxy\Http\CurlTransport;
use BuyProxy\Http\Transport;
use BuyProxy\Resources\Account;
use BuyProxy\Resources\Auth;
use BuyProxy\Resources\Balances;
use BuyProxy\Resources\Plans;
use BuyProxy\Resources\Products;
use BuyProxy\Resources\Reseller;
use BuyProxy\Resources\Topup;
use BuyProxy\Resources\Transactions;

/**
 * BuyProxy API client.
 *
 *     $bp = new BuyProxy\Client(apiKey: getenv('BUYPROXY_API_KEY'));
 *     $result = $bp->plans->generate($planId, country: 'US', count: 5);
 *     $guzzle = new GuzzleHttp\Client($result->proxies[0]->toGuzzle());
 */
final class Client
{
    public const VERSION = '1.0.0';
    public const DEFAULT_BASE_URL = 'https://www.buyproxy.org';

    public readonly ?string $apiKey;
    public readonly Products $products;
    public readonly Auth $auth;
    public readonly Account $account;
    public readonly Balances $balances;
    public readonly Plans $plans;
    public readonly Topup $topup;
    public readonly Transactions $transactions;
    public readonly Reseller $reseller;

    private readonly string $baseUrl;
    private readonly Transport $transport;

    public function __construct(
        ?string $apiKey = null,
        string $baseUrl = self::DEFAULT_BASE_URL,
        private readonly float $timeout = 30.0,
        private readonly int $maxRetries = 2,
        ?Transport $transport = null,
    ) {
        $key = $apiKey ?? (getenv('BUYPROXY_API_KEY') ?: null);
        $this->apiKey = $key === '' ? null : $key;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->transport = $transport ?? new CurlTransport();

        $this->products = new Products($this);
        $this->auth = new Auth($this);
        $this->account = new Account($this);
        $this->balances = new Balances($this);
        $this->plans = new Plans($this);
        $this->topup = new Topup($this);
        $this->transactions = new Transactions($this);
        $this->reseller = new Reseller($this);
    }

    /** `"reseller"` for `bp_live_` keys, `"personal"` for `bp_key_` keys, null without a key. */
    public function keyKind(): ?string
    {
        if ($this->apiKey === null) {
            return null;
        }

        return str_starts_with($this->apiKey, 'bp_live_') ? 'reseller' : 'personal';
    }

    /**
     * @internal Used by the resource classes.
     *
     * @param array<string, scalar|null> $query
     * @param array<string, mixed>|null $body
     * @param array<string, string> $headers
     * @return mixed decoded JSON
     */
    public function request(
        string $method,
        string $path,
        array $query = [],
        ?array $body = null,
        array $headers = [],
        bool $public = false,
        bool $retry5xx = false,
    ): mixed {
        if (!$public && $this->apiKey === null) {
            throw new AuthenticationException(
                'No API key — pass apiKey: to the client or set BUYPROXY_API_KEY. Create one at https://www.buyproxy.org/dashboard/api-keys',
                401,
            );
        }

        $query = array_filter($query, static fn ($v): bool => $v !== null);
        $url = $this->baseUrl . $path . ($query !== [] ? '?' . http_build_query($query) : '');
        $headers = ['Accept' => 'application/json', 'User-Agent' => 'buyproxy-php/' . self::VERSION] + $headers;
        if ($this->apiKey !== null) {
            $headers['X-API-Key'] = $this->apiKey;
        }
        $payload = null;
        if ($body !== null) {
            $headers['Content-Type'] = 'application/json';
            $payload = json_encode($body === [] ? new \stdClass() : $body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        for ($attempt = 0;; $attempt++) {
            try {
                $response = $this->transport->send($method, $url, $headers, $payload, $this->timeout);
            } catch (\BuyProxy\Exceptions\ConnectionException $e) {
                if ($retry5xx && $attempt < $this->maxRetries) {
                    usleep(self::backoffMicros($attempt));
                    continue;
                }
                throw $e;
            }

            $status = $response['status'];
            $decoded = $response['body'] === '' ? null : json_decode($response['body'], true);

            if ($status >= 200 && $status < 300) {
                if ($decoded === null && $response['body'] !== '') {
                    throw new BuyProxyException("BuyProxy returned a non-JSON response (HTTP $status)", $status);
                }

                return $decoded;
            }

            $retryAfter = isset($response['headers']['retry-after']) && is_numeric($response['headers']['retry-after'])
                ? min(30.0, max(0.0, (float) $response['headers']['retry-after']))
                : null;
            $wait = null;
            if ($attempt < $this->maxRetries) {
                if ($status === 429) {
                    $wait = $retryAfter !== null ? (int) ($retryAfter * 1_000_000) : self::backoffMicros($attempt);
                } elseif ($retry5xx && in_array($status, [502, 503, 504], true)) {
                    $wait = self::backoffMicros($attempt);
                }
            }
            if ($wait !== null) {
                usleep($wait);
                continue;
            }

            throw BuyProxyException::fromResponse($status, $decoded, $retryAfter);
        }
    }

    private static function backoffMicros(int $attempt): int
    {
        return (int) (min(500_000 * (2 ** $attempt), 8_000_000) * (0.75 + mt_rand(0, 500) / 1000));
    }
}
