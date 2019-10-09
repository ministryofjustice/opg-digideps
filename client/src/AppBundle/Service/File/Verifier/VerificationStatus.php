<?php

namespace AppBundle\Service\File\Verifier;

class VerificationStatus
{
    /** @var string|null  */
    private $errorMessage = null;

    /**
     * @param $message
     */
    public function addError($message): void
    {
        $this->errorMessage = $message;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return null !== $this->errorMessage;
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->errorMessage;
    }
}
