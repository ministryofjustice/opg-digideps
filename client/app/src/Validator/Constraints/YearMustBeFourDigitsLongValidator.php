<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class YearMustBeFourDigitsLongValidator extends ConstraintValidator
{
    public function validate($data, Constraint $constraint)
    {
        if (!$data instanceof StartEndDateComparableInterface) {
            throw new \InvalidArgumentException(sprintf('Validation data must implement %s interface', StartEndDateComparableInterface::class));
        }

        $startAndEndDate = [$data->getStartDate(), $data->getEndDate()];

        foreach ($startAndEndDate as $date) {
            if (!$date instanceof \DateTime) {
                return;
            }
        }

        foreach ($startAndEndDate as $date) {
            $year = $date->format('Y'); // extract the year from the date string

            $count = 0;
            !preg_match('/^2\d{3}$/', $year) ? $count++ : $count; // if the year does not start with a 2 and is not 4 digits long
        }
        if ($count > 0) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
