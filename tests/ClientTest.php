<?php

declare(strict_types=1);

namespace BuyProxy\Tests;

use BuyProxy\Client;
use BuyProxy\Exceptions\AuthenticationException;
use BuyProxy\Exceptions\InsufficientBandwidthException;
use BuyProxy\Exceptions\NotFoundException;
use BuyProxy\Exceptions\RateLimitException;
use BuyProxy\Exceptions\ServerException;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    private const ACCOUNT = ['email' => 'a@b.co', 'name' => 'A', 'plan' => 'pro', 'planExpiresAt' => null, 'balanceGb' => 4, 'createdAt' => 't'];

    private function client(FakeTransport $t, string $key = 'bp_key_x'): Client
    {
        return new Client(apiKey: $key, transport: $t, maxRetries: 2);
    }

    public function testSendsKeyJsonAndUserAgent(): void
    {
        $t = new FakeTransport([FakeTransport::json(200, ['lines' => ['u:p@gw.buyproxy.org:6969']])]);
        $out = $this->client($t)->plans->generate('plan 1', protocol: 'socks5', count: 1);

        $req = $t->requests[0];
        self::assertSame('https://www.buyproxy.org/v1/plans/plan%201/generate', $req['url']);
        self::assertSame('bp_key_x', $req['headers']['X-API-Key']);
        self::assertStringStartsWith('buyproxy-php/', $req['headers']['User-Agent']);
        self::assertSame('{"protocol":"socks5","count":1}', $req['body']);
        self::assertSame('socks5', $out->proxies[0]->protocol);
    }

    public function testModelsAreTypedAndCoerced(): void
    {
        $t = new FakeTransport([FakeTransport::json(200, self::ACCOUNT)]);
        $a = $this->client($t)->account->get();
        self::assertSame(4.0, $a->balanceGb);
        self::assertNull($a->planExpiresAt);
    }

    public function testRequiresKeyExceptForPublicEndpoints(): void
    {
        $t = new FakeTransport([FakeTransport::json(200, ['products' => [], 'volumeBonus' => ['thresholdGb' => 100, 'rate' => 0.1], 'plans' => []])]);
        putenv('BUYPROXY_API_KEY');
        $bp = new Client(apiKey: '', transport: $t);
        try {
            $bp->account->get();
            self::fail('expected AuthenticationException');
        } catch (AuthenticationException) {
        }
        self::assertSame(100, $bp->products->list()->volumeBonus->thresholdGb);
        self::assertCount(1, $t->requests);
    }

    public function testStatusesMapToExceptions(): void
    {
        foreach ([401 => AuthenticationException::class, 402 => InsufficientBandwidthException::class, 404 => NotFoundException::class] as $status => $class) {
            $t = new FakeTransport([FakeTransport::json($status, ['error' => 'nope'])]);
            try {
                $this->client($t)->account->get();
                self::fail("expected $class");
            } catch (\Throwable $e) {
                self::assertInstanceOf($class, $e);
                self::assertSame('nope', $e->apiMessage());
                self::assertSame($status, $e->status);
            }
        }
    }

    public function testRetries429ThenSucceeds(): void
    {
        $t = new FakeTransport([FakeTransport::json(429, ['error' => 'slow'], ['retry-after' => '0']), FakeTransport::json(200, self::ACCOUNT)]);
        self::assertSame('pro', $this->client($t)->account->get()->plan);
        self::assertCount(2, $t->requests);
    }

    public function testRateLimitExceptionAfterRetries(): void
    {
        $t = new FakeTransport(array_fill(0, 3, FakeTransport::json(429, ['error' => 'slow'], ['retry-after' => '0'])));
        $this->expectException(RateLimitException::class);
        try {
            $this->client($t)->account->get();
        } finally {
            self::assertCount(3, $t->requests);
        }
    }

    public function testPostNotRetriedOn503UnlessIdempotent(): void
    {
        $t = new FakeTransport([FakeTransport::json(503, ['error' => 'down'])]);
        try {
            $this->client($t)->reseller->customers->allocate('c', 'premium', 1);
            self::fail('expected ServerException');
        } catch (ServerException) {
        }
        self::assertCount(1, $t->requests);

        $t = new FakeTransport([FakeTransport::json(503, ['error' => 'down']), FakeTransport::json(201, ['productKey' => 'premium', 'productId' => 'x', 'gb' => 1, 'alreadyApplied' => false, 'poolRemainingGb' => 0])]);
        $a = $this->client($t)->reseller->customers->allocate('c', 'premium', 1, idempotencyKey: 'k1');
        self::assertFalse($a->alreadyApplied);
        self::assertSame('k1', $t->requests[1]['headers']['Idempotency-Key']);
        self::assertSame('{"productKey":"premium","gb":1}', $t->requests[1]['body']);
    }

    public function testTopupCardValidatesArguments(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->client(new FakeTransport([]))->topup->card();
    }

    public function testWaitPollsUntilSettled(): void
    {
        $tx = static fn (string $s): array => FakeTransport::json(200, ['id' => 'st_1', 'kind' => 'topup', 'status' => $s, 'paymentMethod' => 'card', 'interval' => null, 'amountCents' => 1, 'currency' => 'usd', 'gb' => 1, 'productKey' => null, 'plan' => null, 'resellerPool' => false, 'createdAt' => 't']);
        $t = new FakeTransport([$tx('pending'), $tx('succeeded')]);
        self::assertSame('succeeded', $this->client($t)->transactions->wait('st_1', interval: 0.01)->status);
    }

    public function testKeyKind(): void
    {
        self::assertSame('reseller', $this->client(new FakeTransport([]), 'bp_live_x')->keyKind());
        self::assertSame('personal', $this->client(new FakeTransport([]), 'bp_key_x')->keyKind());
    }
}
