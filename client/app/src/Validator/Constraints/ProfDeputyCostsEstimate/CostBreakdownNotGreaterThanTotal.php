<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Validator\Constraints\ProfDeputyCostsEstimate;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class CostBreakdownNotGreaterThanTotal extends Constraint
{
    public string $message = 'profDeputyEstimateCost.profDeputyManagementCostAmount.breakdownGreaterThanTotal';

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
