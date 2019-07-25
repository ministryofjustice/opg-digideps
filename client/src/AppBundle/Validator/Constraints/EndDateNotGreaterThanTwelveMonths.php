<?php

namespace AppBundle\Validator\Constraints;


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

    /**
     * @return array|string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}