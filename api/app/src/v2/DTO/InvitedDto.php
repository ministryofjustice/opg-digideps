<?php

declare(strict_types=1);

namespace App\v2\DTO;

/**
 * Holds data about the outcome of an attempt to create an invite.
 */
class InvitedDto implements \JSONSerializable
{
    public function __construct(
        public readonly string $courtOrderUid,
        public readonly int $invitingUserId,
        public bool $success = false,
        public ?string $message = null,
        public ?string $invitedDeputyUid = null,
        public ?int $invitedUserId = null,

        // error code used to notify the front end about specific error messages which should be displayed,
        // e.g. 422 if the email of the invited deputy already exists in the user table
        public ?int $code = null,
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
                // success is repeated in the body so that callers also have access to it
                'success' => $this->success,
                'message' => $this->message,
                'courtOrderUid' => $this->courtOrderUid,
                'invitingUserId' => $this->invitingUserId,
                'invitedUserId' => $this->invitedUserId,
                'invitedDeputyUid' => $this->invitedDeputyUid,
                'code' => $this->code,
            ],
        ];
    }
}
