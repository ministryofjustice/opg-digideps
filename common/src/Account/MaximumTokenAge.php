<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Account;

enum MaximumTokenAge: int
{
    case PasswordReset = 2;
}
