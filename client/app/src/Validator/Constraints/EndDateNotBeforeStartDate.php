<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class EndDateNotBeforeStartDate extends Constraint
{
    public string $message = 'report.endDate.beforeStart';

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
