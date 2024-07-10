<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EndDateNotGreaterThanFifteenMonthsValidator extends ConstraintValidator
{
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

        $fifteenMonthsFromStart = clone $startDate;
        $fifteenMonthsFromStart->add(new \DateInterval('P1Y3M'));

        if ($endDate > $fifteenMonthsFromStart) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('endDate')->addViolation();
        }
    }
}
