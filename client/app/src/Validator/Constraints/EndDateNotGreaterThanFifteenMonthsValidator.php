<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EndDateNotGreaterThanFifteenMonthsValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof StartEndDateComparableInterface) {
            throw new \InvalidArgumentException(sprintf('Validation data must implement %s interface', StartEndDateComparableInterface::class));
        }

        $startDate = $value->getStartDate();
        $endDate = $value->getEndDate();

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
