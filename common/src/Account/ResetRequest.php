<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Account;

use OPG\Digideps\Common\Validating\ValidatingArray;

final readonly class ResetRequest implements \JsonSerializable
{
    public function __construct(public Email $userEmail)
    {
    }

    public function jsonSerialize(): array
    {
        return ['userEmail' => $this->userEmail];
    }

    public static function jsonDeserialize(string $json): ResetRequest
    {
        $data = new ValidatingArray((array)json_decode($json, true));
        return new ResetRequest(
            new Email($data->getStringOrThrow('userEmail')),
        );
    }
}
