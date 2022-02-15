<?php

declare(strict_types=1);

namespace App\Validator\Constraints\ClientBenefitsCheck;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ClientBenefitsCheck extends Constraint
{
    public string $whenLastCheckedNoOptionSelected = 'form.whenLastChecked.errors.noOptionSelected';
    public string $whenLastCheckedMissingDate = 'form.whenLastChecked.errors.missingDate';
    public string $whenLastCheckedFutureDate = 'form.whenLastChecked.errors.futureDate';
    public string $whenLastCheckedNeverCheckedEntitlementMissingExplanation = 'form.whenLastChecked.errors.missingExplanation';
    public string $whenLastCheckedNeverCheckedEntitlementExplanationTooShort = 'form.whenLastChecked.errors.explanationTooShort';
    public string $moneyOnClientsBehalfNeverCheckedMoneyMissingExplanation = 'form.moneyOnClientsBehalf.errors.missingExplanation';
    public string $moneyOnClientsBehalfNeverCheckedMoneyExplanationTooShort = 'form.moneyOnClientsBehalf.errors.explanationTooShort';
    public string $moneyOnClientsBehalfNoOptionSelected = 'form.moneyOnClientsBehalf.errors.noOptionSelected';
    public string $moneyOnClientsBehalfMissingMoney = 'form.moneyDetails.errors.missingMoney';

    public $mode = 'strict'; // If the constraint has configuration options, define them as public properties
}
