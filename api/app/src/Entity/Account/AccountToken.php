<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Account;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use OPG\Digideps\Common\Account\Token;
use Symfony\Component\Uid\UuidV7;

final class AccountToken
{
    #[Column(type: UuidV7::class, unique: true, index: true)]
    private UuidV7 $uuid;

    public function __construct(
        #[Id, Column(type: 'integer')]
        public readonly int $userId,
        ?Token $token = null
    ) {
        $this->uuid = $token === null ? UuidV7::v7() : $token->uuid;
    }

    public Token $token { get => new Token($this->uuid);}

    public function regenerate(): AccountToken
    {
        $this->uuid = UuidV7::v7();
        return $this;
    }
}
