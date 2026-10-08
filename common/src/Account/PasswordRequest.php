<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Account;

use OPG\Digideps\Common\Validating\ValidatingArray;

final readonly class PasswordRequest implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public Password $password,
        #[\SensitiveParameter] public Token $token,
        #[\SensitiveParameter] public ValidationKey $validationKey,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'password' => $this->password,
            'token' => $this->token,
            'key' => $this->validationKey,
        ];
    }

    public static function jsonDeserialize(string $json): PasswordRequest
    {
        $data = new ValidatingArray((array)json_decode($json, true));
        return new PasswordRequest(
            new Password($data->getStringOrThrow('password')),
            new Token($data->getStringOrThrow('token')),
            new ValidationKey($data->getStringOrThrow('key')),
        );
    }
}
