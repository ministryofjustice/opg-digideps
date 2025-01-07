<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class YearMustBeFourDigits extends Constraint
{
    public string $message = 'Please enter a valid four-digit year.';

    public function validatedBy()
    {
        return 'start_end_dates';
    }

    /**
     * @return array|string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
