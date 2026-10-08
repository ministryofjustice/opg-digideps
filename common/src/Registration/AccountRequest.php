<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Registration;

final readonly class AccountRequest
{
    public function __construct(
        public string $caseNumber,
        public string $deputyFirstName,
        public string $deputyLastName,
        public string $deputyPostCode,
        public string $deputyEmail,
        public string $clientLastName,
    ) {
    }
}
