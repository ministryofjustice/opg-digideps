<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EndDateNotBeforeStartDateValidator extends ConstraintValidator
{
    /**
     * @param mixed $data
     * @param Constraint $constraint
     */
    public function validate($data, Constraint $constraint)
    {
        if (!$data instanceof StartEndDateComparableInterface) {
            throw new \InvalidArgumentException(sprintf('Validation data must implement %s interface', StartEndDateComparableInterface::class));
        }

        $startDate = $data->getStartDate();
        $endDate = $data->getEndDate();

        if (!$startDate instanceof \DateTime || !$endDate instanceof \DateTime) {
            return;
        }

        if ($endDate < $startDate) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->atPath('endDate')->addViolation();
        }
    }
}
