<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Validator\Constraints;

use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class DUserPassword extends UserPassword
{
    public $service = 'security.validator.d_user_password';
}
