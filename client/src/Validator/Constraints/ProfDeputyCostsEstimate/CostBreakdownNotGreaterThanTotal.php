<?php

namespace App\Validator\Constraints\ProfDeputyCostsEstimate;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CostBreakdownNotGreaterThanTotal extends Constraint
{
    /**
     * @var string
     */
    public $message = 'profDeputyEstimateCost.profDeputyManagementCostAmount.breakdownGreaterThanTotal';

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
