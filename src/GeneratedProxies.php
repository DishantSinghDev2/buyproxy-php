<?php

declare(strict_types=1);

namespace BuyProxy;

/**
 * The result of a generate call: raw lines plus parsed proxies.
 *
 * @implements \IteratorAggregate<int, Proxy>
 */
final class GeneratedProxies implements \Countable, \IteratorAggregate
{
    /**
     * @param list<string> $lines
     * @param list<Proxy> $proxies
     */
    public function __construct(public readonly array $lines, public readonly array $proxies)
    {
    }

    /** @param list<string> $lines */
    public static function fromLines(array $lines, string $protocol = 'http'): self
    {
        return new self($lines, array_map(static fn (string $l): Proxy => Proxy::parse($l, $protocol), $lines));
    }

    public function count(): int
    {
        return count($this->proxies);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->proxies);
    }
}
