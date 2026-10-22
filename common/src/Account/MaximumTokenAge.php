<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Account;

/**
 * The unit of the enum value in seconds
 */
enum MaximumTokenAge: int
{
    case PasswordReset = 3600;
}
