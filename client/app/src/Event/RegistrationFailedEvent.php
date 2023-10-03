<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class RegistrationFailedEvent extends Event
{
    public const NAME = 'registration.failed';

    public function __construct(private array $failureData, private string $errorMessage)
    {
        $this->setFailureData($this->failureData);
        $this->setErrorMessage($this->errorMessage);
    }

    public function getFailureData(): array
    {
        return $this->failureData;
    }

    public function setFailureData(array $failureData): RegistrationFailedEvent
    {
        $this->failureData = $failureData;

        return $this;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(string $errorMessage): RegistrationFailedEvent
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }
}
