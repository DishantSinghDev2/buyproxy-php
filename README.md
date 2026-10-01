# BuyProxy PHP SDK — Residential Proxy API Client for Laravel, Symfony & PHP

[![Packagist](https://img.shields.io/packagist/v/buyproxy/buyproxy?logo=packagist&logoColor=white)](https://packagist.org/packages/buyproxy/buyproxy)
[![PHP](https://img.shields.io/packagist/php-v/buyproxy/buyproxy)](https://packagist.org/packages/buyproxy/buyproxy)
[![CI](https://github.com/DishantSinghDev2/buyproxy-php/actions/workflows/ci.yml/badge.svg)](https://github.com/DishantSinghDev2/buyproxy-php/actions/workflows/ci.yml)
[![license](https://img.shields.io/badge/license-MIT-green)](LICENSE)

The official **PHP SDK** for [BuyProxy](https://www.buyproxy.org): generate **residential, mobile, ISP and datacenter proxies**, check bandwidth, top up by card or crypto, and manage **reseller** customers. PHP 8.1+, typed, named arguments, works with **Laravel**, **Symfony**, Guzzle and plain cURL.

**Keywords:** residential proxies · rotating proxies · mobile proxies · ISP proxies · datacenter proxies · SOCKS5 · proxy API · web scraping · proxy reseller

## Features

- Typed `readonly` models and exceptions
- PHP 8 named arguments: `generate($planId, country: 'US', count: 5)`
- Pluggable HTTP transport (cURL by default) — swap in Guzzle or Symfony HttpClient
- Helpers for **Guzzle**, **Laravel `Http`**, **Symfony HttpClient** and raw **cURL**

## Installation

```bash
composer require buyproxy/buyproxy
```

PHP 8.1+ with `ext-curl` and `ext-json`. No other runtime dependencies.

## Quick start

```php
<?php
require 'vendor/autoload.php';

use BuyProxy\Client;

$bp = new Client(apiKey: getenv('BUYPROXY_API_KEY')); // or omit it — reads BUYPROXY_API_KEY

$plan = $bp->plans->list()[0];
$result = $bp->plans->generate($plan->id, country: 'US', city: 'Austin', count: 5);

echo $result->proxies[0]->url(); // http://user-country-us-city-austin:pass@gw.buyproxy.org:6969
```

Pass `stickyMinutes: 10` to keep the same IP for ten minutes, or `protocol: 'socks5'` for SOCKS5.

## Using the proxies with your stack

### Guzzle

```php
$http = new GuzzleHttp\Client($result->proxies[0]->toGuzzle());
echo $http->get('https://api.ipify.org')->getBody();
```

### Laravel

```php
use Illuminate\Support\Facades\Http;

$ip = Http::withOptions($result->proxies[0]->toGuzzle())->get('https://api.ipify.org')->body();
```

Register the client once in a service provider:

```php
$this->app->singleton(\BuyProxy\Client::class, fn () => new \BuyProxy\Client(apiKey: config('services.buyproxy.key')));
```

### Symfony HttpClient

```php
$client = Symfony\Component\HttpClient\HttpClient::create($result->proxies[0]->toSymfony());
echo $client->request('GET', 'https://api.ipify.org')->getContent();
```

### cURL

```php
$ch = curl_init('https://api.ipify.org');
curl_setopt_array($ch, $result->proxies[0]->toCurl() + [CURLOPT_RETURNTRANSFER => true]);
echo curl_exec($ch);
```

## Buy bandwidth (card & crypto)

```php
// Buy 10 GB of Core Residential by card — send the customer to payUrl
$checkout = $bp->topup->card(productKey: 'residential', gb: 10);
header('Location: ' . $checkout->payUrl);

// Or subscribe to a plan (monthly or yearly)
$bp->topup->card(plan: 'pro', interval: 'year');

// Crypto (pay-as-you-go top-ups only)
$bp->topup->crypto('residential', 50);

// Wait for the payment to settle — GB appears on $bp->balances->list() right after
$tx = $bp->transactions->wait($checkout->transactionId);
echo $tx->status; // "succeeded"
```

Card payments run through Stripe Checkout; crypto is available for pay-as-you-go top-ups.

## Reseller API

Build a proxy product on top of BuyProxy: buy bandwidth in bulk into a **reseller pool**, create isolated **customers**, and allocate GB to them. Needs a `bp_live_` key on a Business plan or higher.

```php
$reseller = new Client(apiKey: 'bp_live_…');

$reseller->reseller->pool->topupCard('residential', 500);           // buy into your pool
$customer = $reseller->reseller->customers->create('Acme Inc');     // isolated sub-account

$reseller->reseller->customers->allocate($customer->username, 'residential', 20, idempotencyKey: 'order-1042');

$detail = $reseller->reseller->customers->get($customer->username);
$result = $reseller->reseller->customers->generate($customer->username, $detail->plans[0]->id, country: 'DE');

$reseller->reseller->customers->update($customer->username, status: 'suspended');
```

## Error handling

```php
use BuyProxy\Exceptions\{AuthenticationException, InsufficientBandwidthException, RateLimitException};

try {
    $bp->plans->generate($planId);
} catch (InsufficientBandwidthException) {
    echo 'Out of GB — top up first';
} catch (RateLimitException $e) {
    echo "Slow down; retry in {$e->retryAfterSeconds}s";
} catch (AuthenticationException) {
    echo 'Check your API key';
}
```
Every exception extends `BuyProxy\Exceptions\BuyProxyException` and exposes `->status` and `->body`.

## Configuration

```php
new Client(
    apiKey: 'bp_key_…',                 // default: getenv('BUYPROXY_API_KEY')
    baseUrl: 'https://www.buyproxy.org',
    timeout: 30.0,
    maxRetries: 2,                       // 429s always; 5xx only for safe requests
    transport: new MyGuzzleTransport(),  // implement BuyProxy\Http\Transport
);
```

## API overview

| Capability | Call |
|---|---|
| Catalog & prices | `$bp->products->list()` |
| Account | `$bp->account->get()` |
| Spendable GB per product | `$bp->balances->list()` |
| Plans | `$bp->plans->list()` |
| Generate proxies | `$bp->plans->generate($planId, …)` |
| Card checkout | `$bp->topup->card(…)` |
| Crypto checkout | `$bp->topup->crypto($product, $gb)` |
| Payments | `$bp->transactions->list()` · `->get($id)` · `->wait($id)` |
| Create an account | `$bp->auth->signup()` · `->verifyOtp()` · `->resendOtp()` |
| Reseller summary | `$bp->reseller->me()` |
| Customers | `$bp->reseller->customers->list/create/get/update/usage/allocate/generate` |
| Reseller pool | `$bp->reseller->pool->get/topupCard/topupCrypto` |

Full endpoint reference: <https://www.buyproxy.org/docs/api-reference/introduction> · OpenAPI spec: <https://www.buyproxy.org/openapi.yaml>

## FAQ

**How do I get an API key?**
Create a free account at [buyproxy.org](https://www.buyproxy.org/signup), then open **Dashboard → API keys**. Personal keys start with `bp_key_`; reseller keys (Business plan and above) start with `bp_live_`. You can create the *account* from code (`signup` + `verify otp`), but keys are issued from the dashboard.

**What's the difference between a personal and a reseller key?**
A personal key (`bp_key_…`) manages your own bandwidth: balances, plans, generating proxies, top-ups and payments. A reseller key (`bp_live_…`) manages *your customers*: create isolated sub-accounts, buy bandwidth into a pool, allocate it, suspend or reactivate, and generate proxies on their behalf.

**Which proxy types are available?**
Core Residential, Premium Residential, ISP Rotating, Mobile, Datacenter IPv4 and IPv6 — call the catalog endpoint for the live list and per-GB prices (from **$1.99/GB**).

**Do generated proxies expire, and when is bandwidth used?**
Generating a proxy is free and instant; bandwidth is only consumed when traffic flows through it. Lines are on-demand — generate fresh ones rather than storing them.

**What are the rate limits?**
60 requests per minute per API key. The SDK honours `Retry-After` and retries rate-limited requests automatically.

**Is it free to use the SDK?**
Yes — the SDK is MIT-licensed. You pay only for the proxy bandwidth you buy.

## Other BuyProxy SDKs

[Node.js](https://github.com/DishantSinghDev2/buyproxy-node) · [Python](https://github.com/DishantSinghDev2/buyproxy-python) · [Go](https://github.com/DishantSinghDev2/buyproxy-go) · [PHP](https://github.com/DishantSinghDev2/buyproxy-php) · [Ruby](https://github.com/DishantSinghDev2/buyproxy-ruby) · [Java](https://github.com/DishantSinghDev2/buyproxy-java) · [.NET](https://github.com/DishantSinghDev2/buyproxy-dotnet) · [Rust](https://github.com/DishantSinghDev2/buyproxy-rust) · [CLI](https://github.com/DishantSinghDev2/buyproxy-cli)

All SDKs are checked against the same request log, so behaviour is identical across languages — see [buyproxy-sdks](https://github.com/DishantSinghDev2/buyproxy-sdks).

## Development

```bash
composer install
vendor/bin/phpunit
```

Issues and pull requests are welcome — see [CONTRIBUTING.md](CONTRIBUTING.md). Security reports: [SECURITY.md](SECURITY.md).

## Links

- Website: <https://www.buyproxy.org>
- Documentation: <https://www.buyproxy.org/docs/sdks>
- Pricing: <https://www.buyproxy.org/pricing>
- Support: <support@buyproxy.org>

## License

[MIT](LICENSE) © BuyProxy
