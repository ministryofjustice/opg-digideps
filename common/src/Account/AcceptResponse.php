<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Account;

use OPG\Digideps\Common\Validating\ValidatingArray;

final readonly class AcceptResponse implements \JsonSerializable
{
    public function __construct()
    {
    }

    public function jsonSerialize(): array
    {
        return ['error' => null];
    }

    public static function jsonDeserialize(string $json): AcceptResponse|AccountError
    {
        $data = new ValidatingArray((array)json_decode($json, true));
        $error = $data->getStringOrNull('error');
        if ($error !== null) {
            return AccountError::from($error);
        }
        return new AcceptResponse();
    }
}
