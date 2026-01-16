<?php

declare(strict_types=1);

namespace App\Factory;

class DataFactoryResult
{
    public function __construct(
        /** @var array<string, array<int, string>> $messages */
        private array $messages = [],
        /** @var array<string, array<int, string>> $errorMessages */
        private array $errorMessages = [],
    ) {
    }

    public function addMessages(string $source, array $messages): void
    {
        if (empty($messages)) {
            return;
        }

        $this->messages[$source] = array_merge($this->messages[$source] ?? [], $messages);
    }

    public function addErrorMessages(string $source, array $errorMessages): void
    {
        if (empty($errorMessages)) {
            return;
        }

        $this->errorMessages[$source] = array_merge($this->errorMessages[$source] ?? [], $errorMessages);
    }

    /**
     * @return array<string, array<int, string>> A map from source => array of messages
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @return array<string, array<int, string>> A map from source => array of error messages
     */
    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    public function getSuccess(): bool
    {
        return empty($this->errorMessages);
    }
}
