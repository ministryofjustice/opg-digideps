<?php

namespace OPG\Digideps\Frontend\Validator\Constraints\ProfDeputyCostsEstimate;

use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CostBreakdownNotGreaterThanTotalValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof Report) {
            throw new \InvalidArgumentException(sprintf('Validation data must be an instance of %s', Report::class));
        }

        $totalNotToExceed = $value->getProfDeputyManagementCostAmount();
        $valueToVerify = 0;

        foreach ($value->getProfDeputyEstimateCosts() as $profDeputyEstimateCost) {
            $valueToVerify += (float)$profDeputyEstimateCost->getAmount();
        }

        if ($valueToVerify > $totalNotToExceed) {
            $this->context->addViolation($constraint->message);
        }
    }
}
