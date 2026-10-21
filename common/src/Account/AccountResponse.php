<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Account;

use OPG\Digideps\Common\Deputy\DeputyType;
use OPG\Digideps\Common\Validating\ValidatingArray;

final readonly class AccountResponse implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public Token $token,
        #[\SensitiveParameter] public ValidationKey $validationKey,
        #[\SensitiveParameter] public Email $email,
        public Area $area,
        public DeputyType $deputyType
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'token' => $this->token,
            'key' => $this->validationKey,
            'email' => $this->email,
            'area' => $this->area->value,
            'type' => $this->deputyType->value,
        ];
    }

    public static function jsonDeserialize(string $json): AccountResponse|AccountError
    {
        $data = new ValidatingArray((array)json_decode($json, true));
        $error = $data->getStringOrNull('error');
        if ($error !== null) {
            return AccountError::from($error);
        }
        return new AccountResponse(
            new Token($data->getStringOrThrow('token')),
            new ValidationKey($data->getStringOrThrow('key')),
            new Email($data->getStringOrThrow('email')),
            Area::from($data->getStringOrThrow('area')),
            DeputyType::from($data->getStringOrThrow('type')),
        );
    }
}
