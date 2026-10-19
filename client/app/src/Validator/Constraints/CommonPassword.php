<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class CommonPassword extends Constraint
{
    public string $message = 'Your password is too easy for someone to guess. Please choose a different password.';
}
