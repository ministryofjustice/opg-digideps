<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class EndDateNotGreaterThanFifteenMonths extends Constraint
{
    /**
     * @var string
     */
    public $message = 'report.endDate.greaterThan15Months';

    /**
     * @return array|string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
