<?php

namespace App\Service\File\Verifier;

class VerificationStatus
{
    /** @var string|null */
    private $errorMessage;

    /** @var int */
    private $status;

    const FAILED = 0;
    const PASSED = 1;

    /**
     * @param $message
     */
    public function addError($message): void
    {
        $this->errorMessage = $message;
        $this->status = self::FAILED;
    }

    public function getStatus(): int
    {
        return self::FAILED === $this->status ? self::FAILED : self::PASSED;
    }

    public function getError(): ?string
    {
        return $this->errorMessage;
    }
}
