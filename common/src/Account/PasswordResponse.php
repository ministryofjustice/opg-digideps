<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Account;

use OPG\Digideps\Common\Deputy\DeputyType;
use OPG\Digideps\Common\Validating\ValidatingArray;

final readonly class PasswordResponse implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public Email $email,
        public Area $area,
        public DeputyType $deputyType
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'email' => $this->email,
            'area' => $this->area->value,
            'type' => $this->deputyType->value,
        ];
    }

    public static function jsonDeserialize(string $json): PasswordResponse|AccountError
    {
        $data = new ValidatingArray((array)json_decode($json, true));
        $error = $data->getStringOrNull('error');
        if ($error !== null) {
            return AccountError::from($error);
        }
        return new PasswordResponse(
            new Email($data->getStringOrThrow('email')),
            Area::from($data->getStringOrThrow('area')),
            DeputyType::from($data->getStringOrThrow('type')),
        );
    }
}
