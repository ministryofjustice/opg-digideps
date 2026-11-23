<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Account;

use OPG\Digideps\Common\Validating\ValidatingArray;

final readonly class AcceptRequest implements \JsonSerializable
{
    public function __construct(public int $userId)
    {
    }

    public function jsonSerialize(): array
    {
        return ['user_id' => $this->userId];
    }

    public static function jsonDeserialize(string $json): AcceptRequest
    {
        $data = new ValidatingArray((array)json_decode($json, true));
        return new AcceptRequest($data->getIntegerOrThrow('user_id'));
    }
}
