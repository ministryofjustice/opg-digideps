<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class StrongPassword extends Constraint
{
    public string $message = 'Your password is too weak. Please choose a different password.';
}
