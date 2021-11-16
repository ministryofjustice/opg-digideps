<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class EndDateNotGreaterThanTwelveMonths extends Constraint
{
    /**
     * @var string
     */
    public $message = 'report.endDate.greaterThan12Months';

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
