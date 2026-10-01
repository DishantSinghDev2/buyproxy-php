<?php

declare(strict_types=1);

namespace BuyProxy\Exceptions;

/** Base class for every exception thrown by the SDK. */
class BuyProxyException extends \RuntimeException
{
    /**
     * @param mixed $body Parsed JSON error body, when the API sent one
     */
    public function __construct(
        string $message,
        public readonly ?int $status = null,
        public readonly mixed $body = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($status !== null ? "$message (HTTP $status)" : $message, $status ?? 0, $previous);
    }

    /** The API's own error text, without the HTTP suffix. */
    public function apiMessage(): string
    {
        return is_array($this->body) && isset($this->body['error']) && is_string($this->body['error'])
            ? $this->body['error']
            : $this->getMessage();
    }

    public static function fromResponse(int $status, mixed $body, ?float $retryAfter = null): self
    {
        $message = is_array($body) && isset($body['error']) && is_string($body['error']) ? $body['error'] : 'BuyProxy API error';

        return match (true) {
            $status === 400 => new ValidationException($message, $status, $body),
            $status === 401 => new AuthenticationException($message, $status, $body),
            $status === 402 => new InsufficientBandwidthException($message, $status, $body),
            $status === 403 => new PermissionDeniedException($message, $status, $body),
            $status === 404 => new NotFoundException($message, $status, $body),
            $status === 429 => new RateLimitException($message, $body, $retryAfter),
            $status >= 500 => new ServerException($message, $status, $body),
            default => new self($message, $status, $body),
        };
    }
}
