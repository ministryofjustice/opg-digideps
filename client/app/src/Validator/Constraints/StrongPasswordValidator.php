<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Validator\Constraints;

use OPG\Digideps\Common\Account\Password;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class StrongPasswordValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (is_string($value) && $constraint instanceof StrongPassword && !Password::isValid($value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
