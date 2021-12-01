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
    public string $incomeOnClientsBehalfNeverCheckedIncomeMissingExplanation = 'form.incomeOnClientsBehalf.errors.missingExplanation';
    public string $incomeOnClientsBehalfNeverCheckedIncomeExplanationTooShort = 'form.incomeOnClientsBehalf.errors.explanationTooShort';
    public string $incomeOnClientsBehalfMissingIncome = 'form.incomeDetails.errors.missingIncome';

    public $mode = 'strict'; // If the constraint has configuration options, define them as public properties
}
