<?php

declare(strict_types=1);

namespace BuyProxy\Http;

/**
 * Pluggable HTTP layer. The default is {@see CurlTransport}; implement this to use Guzzle,
 * Symfony HttpClient, a framework's client, or a fake in tests.
 */
interface Transport
{
    /**
     * @param array<string, string> $headers
     * @return array{status: int, headers: array<string, string>, body: string} header names lower-cased
     * @throws \BuyProxy\Exceptions\ConnectionException when no response was received
     */
    public function send(string $method, string $url, array $headers, ?string $body, float $timeout): array;
}
