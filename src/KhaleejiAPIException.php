<?php

declare(strict_types=1);

namespace KhaleejiAPI;

/**
 * Exception thrown by the KhaleejiAPI SDK.
 */
class KhaleejiAPIException extends \Exception
{
    private string $errorCode;
    private string $messageEn;
    private string $messageAr;
    private ?array $rateLimitInfo;

    public function __construct(
        string $message,
        int $statusCode = 0,
        string $errorCode = 'UNKNOWN',
        ?string $messageEn = null,
        ?string $messageAr = null,
        ?array $rateLimitInfo = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
        $this->errorCode = $errorCode;
        $this->messageEn = $messageEn ?? $message;
        $this->messageAr = $messageAr ?? $this->messageEn;
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

    public function getMessageEn(): string
    {
        return $this->messageEn;
    }

    public function getMessageAr(): string
    {
        return $this->messageAr;
    }

    public function getLocalizedMessage(string $locale = 'en'): string
    {
        return $locale === 'ar' ? $this->messageAr : $this->messageEn;
    }

    /**
     * @return array{limit: ?string, remaining: ?string, reset: ?string}|null
     */
    public function getRateLimitInfo(): ?array
    {
        return $this->rateLimitInfo;
    }
}
