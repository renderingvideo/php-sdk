<?php

declare(strict_types=1);

namespace RenderingVideo\SDK\Exceptions;

/**
 * Base exception for all SDK exceptions
 */
class ApiException extends \Exception
{
    protected string $errorCode;
    protected int $httpStatus;
    protected array $details;

    public function __construct(
        string $message,
        string $errorCode = 'UNKNOWN_ERROR',
        int $httpStatus = 0,
        array $details = []
    ) {
        parent::__construct($message, $httpStatus);
        $this->errorCode = $errorCode;
        $this->httpStatus = $httpStatus;
        $this->details = $details;
    }

    /**
     * Get the error code
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Get the HTTP status code
     */
    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    /**
     * Get additional error details
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * Check if this is a specific error code
     */
    public function is(string $errorCode): bool
    {
        return $this->errorCode === $errorCode;
    }
}
