<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class CommonPassword extends Constraint
{
    public $message = 'Your password is too easy for someone to guess. Please choose a different password.';
}
