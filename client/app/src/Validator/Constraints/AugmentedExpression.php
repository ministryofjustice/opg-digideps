<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Validator\Constraints;

use OPG\Digideps\Frontend\Service\Expression\AugmentedExpressionValidator;
use Symfony\Component\Validator\Constraints\Expression;

final class AugmentedExpression extends Expression
{
    public function validatedBy(): string
    {
        return AugmentedExpressionValidator::class;
    }
}
