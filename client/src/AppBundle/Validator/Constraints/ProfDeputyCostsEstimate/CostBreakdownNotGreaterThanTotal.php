<?php

namespace AppBundle\Validator\Constraints\ProfDeputyCostsEstimate;

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

    /**
     * @return array|string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
