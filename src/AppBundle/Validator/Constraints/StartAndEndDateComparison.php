<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class StartAndEndDateComparison extends Constraint
{
    /**
     * @var string
     */
    public $message = 'End date cannot be before the start date';

    /**
     * @return array|string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
