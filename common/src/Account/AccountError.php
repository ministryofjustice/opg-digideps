<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Account;

enum AccountError: string implements \JsonSerializable
{
    case MatchingError = 'MatchingError';
    case AccountTaken = 'AccountTaken';
    case PasswordSet = 'PasswordSet';
    case TokenInvalid = 'TokenInvalid';
    case EmailError = 'EmailError';

    public function jsonSerialize(): array
    {
        return ['error' => $this->value];
    }
}
