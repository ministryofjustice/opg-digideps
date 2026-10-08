<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Account;

use Symfony\Component\Uid\UuidV7;

final class Token implements \Stringable, \JsonSerializable
{
    public readonly UuidV7 $uuid;

    public function __construct(null|UuidV7|string $token = null)
    {
        if ($token === null) {
            $this->uuid = UuidV7::v7();
        } elseif ($token instanceof UuidV7) {
            $this->uuid = $token;
        } elseif (self::isValid($token)) {
            $this->uuid = UuidV7::fromString($token);
        } else {
            throw new \DomainException("Invalid token string provided");
        }
    }

    /**
     * Age in whole days since creation. (->days never returns false as used here)
     */
    public int $age { get => $this->uuid->getDateTime()->diff(new \DateTimeImmutable())->days ?: 0 ;}

    public function isExpired(MaximumTokenAge $purpose): bool
    {
        return $this->age > $purpose->value;
    }

    public function __toString(): string
    {
        return $this->uuid->toRfc4122();
    }

    public function jsonSerialize(): string
    {
        return "{$this}";
    }

    public static function isValid(string $value): bool
    {
        return UuidV7::isValid($value);
    }
}
