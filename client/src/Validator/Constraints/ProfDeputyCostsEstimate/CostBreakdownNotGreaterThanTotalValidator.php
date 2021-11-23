<?php

namespace App\Validator\Constraints\ProfDeputyCostsEstimate;

use App\Entity\ReportInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CostBreakdownNotGreaterThanTotalValidator extends ConstraintValidator
{
    /**
     * @param mixed $data
     */
    public function validate($data, Constraint $constraint)
    {
        if (!$data instanceof ReportInterface) {
            throw new \InvalidArgumentException(sprintf('Validation data must implement %s interface', ReportInterface::class));
        }

        $totalNotToExceed = $data->getProfDeputyManagementCostAmount();
        $valueToVerify = 0;

        foreach ($data->getProfDeputyEstimateCosts() as $profDeputyEstimateCost) {
            $valueToVerify += $profDeputyEstimateCost->getAmount();
        }

        if ($valueToVerify > $totalNotToExceed) {
            $this->context->addViolation($constraint->message);
        }
    }
}
