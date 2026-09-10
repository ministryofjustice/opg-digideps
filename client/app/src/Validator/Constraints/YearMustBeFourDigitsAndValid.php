<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class YearMustBeFourDigitsAndValid extends Constraint
{
    public string $message = 'Please enter a valid four-digit year.';

    public function validatedBy(): string
    {
        return static::class . 'Validator';
    }

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
