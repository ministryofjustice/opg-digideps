<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class EndDateNotBeforeStartDate extends Constraint
{
    /**
     * @var string
     */
    public $message = 'report.endDate.beforeStart';

    /**
     * @return array|string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
