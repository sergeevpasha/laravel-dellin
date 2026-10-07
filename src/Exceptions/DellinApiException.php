<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Dellin did not answer the request: transport error or timeout, rate limit, server error,
 * a body that is not JSON, or a session Dellin keeps rejecting.
 */
class DellinApiException extends RuntimeException
{
    private ?int $status;

    public function __construct(string $path, ?int $status, string $details, ?Throwable $previous = null)
    {
        $this->status = $status;

        parent::__construct(
            "Dellin API $path" . ($status !== null ? ": HTTP $status" : '') . " - $details",
            $status ?? 0,
            $previous
        );
    }

    public static function transport(string $path, Throwable $previous): self
    {
        return new self($path, null, $previous->getMessage(), $previous);
    }

    public static function response(string $path, int $status, ?array $body, string $raw = ''): self
    {
        $details = $body !== null ? json_encode($body, JSON_UNESCAPED_UNICODE) : $raw;

        return new self($path, $status, mb_substr((string) $details, 0, 300));
    }

    /**
     * HTTP status, null when no response was received.
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }
}
