<?php

namespace App\v2\DTO;

final readonly class DeputyItemDto
{
    public function __construct(
        public ?\DateTimeImmutable $lastLoggedIn,
        public ?string $firstname,
        public ?string $lastname,
        public string $email,
    ) {
    }
}
