<?php

namespace App\Validator\Constraints\ProfDeputyCostsEstimate;

use App\Entity\ReportInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CostBreakdownNotGreaterThanTotalValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof ReportInterface) {
            throw new \InvalidArgumentException(sprintf('Validation data must implement %s interface', ReportInterface::class));
        }

        $totalNotToExceed = $value->getProfDeputyManagementCostAmount();
        $valueToVerify = 0;

        foreach ($value->getProfDeputyEstimateCosts() as $profDeputyEstimateCost) {
            $valueToVerify += $profDeputyEstimateCost->getAmount();
        }

        if ($valueToVerify > $totalNotToExceed) {
            $this->context->addViolation($constraint->message);
        }
    }
}
