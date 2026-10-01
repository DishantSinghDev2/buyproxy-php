<?php

declare(strict_types=1);

namespace BuyProxy\Tests;

use BuyProxy\GeneratedProxies;
use BuyProxy\Proxy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProxyTest extends TestCase
{
    public function testParsesKeepingAtAndColonInPassword(): void
    {
        $line = 'user-country-us:p@ss:w0rd@gw.buyproxy.org:6969';
        $p = Proxy::parse($line);
        self::assertSame(['gw.buyproxy.org', 6969, 'user-country-us', 'p@ss:w0rd'], [$p->host, $p->port, $p->username, $p->password]);
        self::assertSame($line, (string) $p);
    }

    public function testUrlPercentEncodesCredentials(): void
    {
        self::assertSame('http://u:p%40ss@h.example:80', Proxy::parse('u:p@ss@h.example:80')->url());
        self::assertSame('socks5://h.example:1080', Proxy::parse('u:p@h.example:1080', 'socks5')->server());
    }

    public function testHelpers(): void
    {
        $p = Proxy::parse('u:p@h.example:8080');
        self::assertSame(['proxy' => 'http://u:p@h.example:8080'], $p->toGuzzle());
        self::assertSame('http://h.example:8080', $p->toCurl()[CURLOPT_PROXY]);
        self::assertSame('http://u:p@h.example:8080', $p->env()['HTTPS_PROXY']);
    }

    /** @return iterable<string, array{string}> */
    public static function badLines(): iterable
    {
        foreach (['', 'nope', 'u:p@host', 'u:p@host:99999', ':p@host:80', 'u@host:80'] as $l) {
            yield "'$l'" => [$l];
        }
    }

    #[DataProvider('badLines')]
    public function testRejectsMalformedLines(string $line): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Proxy::parse($line);
    }

    public function testGeneratedProxiesIsCountableAndIterable(): void
    {
        $g = GeneratedProxies::fromLines(['a:b@h:1', 'c:d@h:2']);
        self::assertCount(2, $g);
        self::assertSame([1, 2], array_map(static fn (Proxy $p): int => $p->port, iterator_to_array($g)));
    }
}
