<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Account;

final readonly class Password implements \JsonSerializable
{
    public function __construct(public string $value)
    {
        if (!self::isValid($this->value)) {
            throw new \DomainException('Invalid password passed');
        }
    }

    public function jsonSerialize(): string
    {
        return $this->value; //Might be worth encrypting for transit
    }

    public static function isValid(string $value): bool
    {
        return !empty($value);
    }
}
