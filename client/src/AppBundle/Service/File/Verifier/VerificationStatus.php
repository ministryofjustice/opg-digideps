<?php

namespace AppBundle\Service\File\Verifier;

class VerificationStatus
{
    /** @var string|null  */
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

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status === self::FAILED ? self::FAILED : self::PASSED;
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->errorMessage;
    }
}
