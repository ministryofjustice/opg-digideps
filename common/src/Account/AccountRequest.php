<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Account;

use OPG\Digideps\Common\Validating\ValidatingArray;

final readonly class AccountRequest implements \JsonSerializable
{
    public function __construct(
        public NonEmptyString $caseNumber,
        #[\SensitiveParameter] public NonEmptyString $deputyFirstname,
        #[\SensitiveParameter] public NonEmptyString $deputyLastName,
        #[\SensitiveParameter] public NonEmptyString $deputyPostCode,
        #[\SensitiveParameter] public Email $deputyEmail,
        #[\SensitiveParameter] public NonEmptyString $clientLastName,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'caseNumber' => $this->caseNumber,
            'deputyFirstname' => $this->deputyFirstname,
            'deputyLastName' => $this->deputyLastName,
            'deputyPostCode' => $this->deputyPostCode,
            'deputyEmail' => $this->deputyEmail,
            'clientLastName' => $this->clientLastName,
        ];
    }

    public static function jsonDeserialize(string $json): AccountRequest
    {
        $data = new ValidatingArray((array)json_decode($json, true));
        return new AccountRequest(
            new NonEmptyString($data->getStringOrThrow('caseNumber')),
            new NonEmptyString($data->getStringOrThrow('deputyFirstname')),
            new NonEmptyString($data->getStringOrThrow('deputyLastName')),
            new NonEmptyString($data->getStringOrThrow('deputyPostCode')),
            new Email($data->getStringOrThrow('deputyEmail')),
            new NonEmptyString($data->getStringOrThrow('clientLastName')),
        );
    }
}
