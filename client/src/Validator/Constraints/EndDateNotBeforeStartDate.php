<?php

namespace App\Validator\Constraints;

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

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
