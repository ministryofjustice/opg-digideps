<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class EndDateNotGreaterThanFifteenMonths extends Constraint
{
    public string $message = 'report.endDate.greaterThan15Months';

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
