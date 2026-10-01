<?php

declare(strict_types=1);

namespace BuyProxy;

/** One generated proxy. The API returns `username:password@host:port` lines. */
final class Proxy implements \Stringable
{
    public function __construct(
        public readonly string $host,
        public readonly int $port,
        public readonly string $username,
        public readonly string $password,
        public readonly string $protocol = 'http',
    ) {
    }

    /** Parse a `username:password@host:port` line (`@` and `:` are allowed inside the password). */
    public static function parse(string $line, string $protocol = 'http'): self
    {
        $at = strrpos($line, '@');
        $bad = new \InvalidArgumentException("Not a proxy line (expected username:password@host:port): $line");
        if ($at === false) {
            throw $bad;
        }
        $credentials = substr($line, 0, $at);
        $address = substr($line, $at + 1);
        $colon = strpos($credentials, ':');
        $lastColon = strrpos($address, ':');
        if ($colon === false || $colon < 1 || $lastColon === false || $lastColon < 1) {
            throw $bad;
        }
        $port = substr($address, $lastColon + 1);
        if (!ctype_digit($port) || (int) $port < 1 || (int) $port > 65535) {
            throw $bad;
        }

        return new self(substr($address, 0, $lastColon), (int) $port, substr($credentials, 0, $colon), substr($credentials, $colon + 1), $protocol);
    }

    /** `username:password@host:port` — the form the API uses. */
    public function __toString(): string
    {
        return "{$this->username}:{$this->password}@{$this->host}:{$this->port}";
    }

    /** `http://host:port` — no credentials. */
    public function server(): string
    {
        return "{$this->protocol}://{$this->host}:{$this->port}";
    }

    /** Full URL with percent-encoded credentials, ready for any HTTP client. */
    public function url(): string
    {
        return $this->protocol . '://' . rawurlencode($this->username) . ':' . rawurlencode($this->password) . "@{$this->host}:{$this->port}";
    }

    /** Guzzle: `new GuzzleHttp\Client($proxy->toGuzzle())`. */
    public function toGuzzle(): array
    {
        return ['proxy' => $this->url()];
    }

    /** Symfony HttpClient: `HttpClient::create($proxy->toSymfony())`. */
    public function toSymfony(): array
    {
        return ['proxy' => $this->url()];
    }

    /** Laravel: `Http::withOptions($proxy->toGuzzle())->get(...)`. Raw cURL: `curl_setopt_array($ch, $proxy->toCurl())`. */
    public function toCurl(): array
    {
        return [
            CURLOPT_PROXY => $this->server(),
            CURLOPT_PROXYTYPE => $this->protocol === 'socks5' ? CURLPROXY_SOCKS5_HOSTNAME : CURLPROXY_HTTP,
            CURLOPT_PROXYUSERPWD => "{$this->username}:{$this->password}",
        ];
    }

    /** @return array<string, string> environment variables understood by curl, wget, git and most CLIs */
    public function env(): array
    {
        $url = $this->url();

        return array_fill_keys(['HTTP_PROXY', 'HTTPS_PROXY', 'ALL_PROXY', 'http_proxy', 'https_proxy', 'all_proxy'], $url);
    }
}
