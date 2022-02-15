<?php

declare(strict_types=1);

namespace App\Validator\Constraints\ClientBenefitsCheck;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class MoneyReceivedOnClientsBehalf extends Constraint
{
    public string $moneyDetailsMissingMoneyTypeMessage = 'form.incomeDetails.errors.missingType';
    public string $moneyDetailsMissingAmountMessage = 'form.incomeDetails.errors.missingAmount';
    public string $moneyDetailsAmountAndDontKnowMessage = 'form.incomeDetails.errors.amountAndDontKnow';

    public $mode = 'strict'; // If the constraint has configuration options, define them as public properties
}
