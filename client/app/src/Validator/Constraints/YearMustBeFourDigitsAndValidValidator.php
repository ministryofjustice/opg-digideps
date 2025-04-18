<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class YearMustBeFourDigitsAndValidValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        $startAndEndDate = [];
        $courtDate = new \DateTime();

        if ($value instanceof StartEndDateComparableInterface) {
            $startAndEndDate = [$value->getStartDate(), $value->getEndDate()];

            foreach ($startAndEndDate as $date) {
                if (!$date instanceof \DateTime) {
                    return;
                }
            }
        } else {
            $courtDate = $value->getCourtDate();

            if (!$courtDate instanceof \DateTime) {
                return;
            }
        }

        if ($startAndEndDate) {
            $count = count(array_filter($startAndEndDate, function ($date) {
                $year = $date->format('Y');

                // if the year does not start with a 2 and is not 4 digits long add to count
                return !preg_match('/^2\d{3}$/', $year);
            }));

            if ($count > 0) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->addViolation();
            }
        } else {
            $year = $courtDate->format('Y');

            if (!preg_match('/^2\d{3}$/', $year) && !preg_match('/^19\d{2}$/', $year)) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->addViolation();
            }
        }
    }
}
