<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Account;

/**
 * The unit of the enum value is days
 */
enum MaximumTokenAge: int
{
    case PasswordReset = 2;
}
