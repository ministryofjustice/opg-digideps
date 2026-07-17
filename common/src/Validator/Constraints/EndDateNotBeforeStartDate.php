<?php

namespace OPG\Digideps\Common\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
class EndDateNotBeforeStartDate extends Constraint
{
    public string $message = 'report.endDate.beforeStart';

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
