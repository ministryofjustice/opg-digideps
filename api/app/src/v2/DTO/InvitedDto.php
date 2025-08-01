<?php

declare(strict_types=1);

namespace App\v2\DTO;

/**
 * Holds data about the outcome of an attempt to create an invite.
 */
class InvitedDto implements \JSONSerializable
{
    public function __construct(
        public bool $success = false,
        public ?string $message = null,
        public ?string $registrationToken = null,
    ) {
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'success' => $this->success,
            'data' => [
                'message' => $this->message,
                'registrationToken' => $this->registrationToken,
            ],
        ];
    }
}
