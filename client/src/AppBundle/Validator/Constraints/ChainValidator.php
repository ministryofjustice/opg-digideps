<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ChainValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $group = $this->context->getGroup();
        $propertyPath = $this->context->getPropertyPath();

        $violationList = $this->context->getViolations();
        $violationCountPrevious = $violationList->count();

        foreach ($constraint->constraints as $constr) {
            $this->context->validateValue($value, $constr, $propertyPath, $group);

            if ($constraint->stopOnError && (count($violationList) !== $violationCountPrevious)) {
                return;
            }
        }
    }
}
