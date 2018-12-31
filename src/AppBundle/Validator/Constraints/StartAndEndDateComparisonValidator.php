<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class StartAndEndDateComparisonValidator extends ConstraintValidator
{
    /**
     * @param mixed $data
     * @param Constraint $constraint
     */
    public function validate($data, Constraint $constraint)
    {
        $fromDate = $data->getFromDate();
        $toData = $data->getToDate();

        if (!$fromDate instanceof \DateTime || !$toData instanceof \DateTime) {
            return;
        }

        if ($toData < $fromDate) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
