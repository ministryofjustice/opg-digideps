<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Account;

final readonly class ValidationKey implements \Stringable, \JsonSerializable
{
    public const int LENGTH = 32;

    public function __construct(
        public string $value
    ) {
        if (!self::isValid($this->value)) {
            throw new \DomainException("Passed string is of incorrect length");
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
        return strlen($value) === self::LENGTH;
    }
}
