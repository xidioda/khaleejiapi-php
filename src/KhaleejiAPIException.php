<?php

declare(strict_types=1);

namespace KhaleejiAPI;

/**
 * Exception thrown by the KhaleejiAPI SDK.
 */
class KhaleejiAPIException extends \Exception
{
    private string $errorCode;
    private ?array $rateLimitInfo;

    public function __construct(
        string $message,
        int $statusCode = 0,
        string $errorCode = 'UNKNOWN',
        ?array $rateLimitInfo = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
        $this->errorCode = $errorCode;
        $this->rateLimitInfo = $rateLimitInfo;
    }

    public function getStatusCode(): int
    {
        return $this->getCode();
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * @return array{limit: ?string, remaining: ?string, reset: ?string}|null
     */
    public function getRateLimitInfo(): ?array
    {
        return $this->rateLimitInfo;
    }
}
