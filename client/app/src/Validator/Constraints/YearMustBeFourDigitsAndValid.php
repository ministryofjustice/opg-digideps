<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class YearMustBeFourDigitsAndValid extends Constraint
{
    public string $message = 'Please enter a valid four-digit year.';

    public function validatedBy()
    {
        return static::class.'Validator';
    }

    /**
     * @return array|string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
