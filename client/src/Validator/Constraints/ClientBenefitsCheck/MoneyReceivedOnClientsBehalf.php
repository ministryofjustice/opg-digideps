<?php

declare(strict_types=1);

namespace App\Validator\Constraints\ClientBenefitsCheck;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class MoneyReceivedOnClientsBehalf extends Constraint
{
    public string $moneyDetailsMissingMoneyTypeMessage = 'form.moneyDetails.errors.missingType';
    public string $moneyDetailsMissingAmountMessage = 'form.moneyDetails.errors.missingAmount';
    public string $moneyDetailsAmountAndDontKnowMessage = 'form.moneyDetails.errors.amountAndDontKnow';

    public $mode = 'strict'; // If the constraint has configuration options, define them as public properties
}
