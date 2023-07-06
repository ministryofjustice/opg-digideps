<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class YesNoNa extends Constraint
{
    public $message = 'checklist.yesNoNa';
}
