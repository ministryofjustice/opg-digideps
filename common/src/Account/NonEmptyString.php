<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Account;

final readonly class NonEmptyString implements \Stringable, \JsonSerializable
{
    public function __construct(
        public string $value
    ) {
        if (!self::isValid($this->value)) {
            throw new \DomainException('Empty string passed');
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return "{$this}";
    }

    public static function isValid(string $value): bool
    {
        return !empty($value);
    }
}
