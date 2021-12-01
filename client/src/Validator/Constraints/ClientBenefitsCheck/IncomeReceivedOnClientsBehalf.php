<?php

declare(strict_types=1);

namespace App\Validator\Constraints\ClientBenefitsCheck;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IncomeReceivedOnClientsBehalf extends Constraint
{
    public string $incomeDetailsMissingAmountMessage = 'form.incomeDetails.errors.missingAmount';
    public string $incomeDetailsAmountAndDontKnowMessage = 'form.incomeDetails.errors.amountAndDontKnow';

    public $mode = 'strict'; // If the constraint has configuration options, define them as public properties
}
