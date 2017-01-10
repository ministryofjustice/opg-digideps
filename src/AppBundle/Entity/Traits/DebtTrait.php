<?php

namespace AppBundle\Entity\Traits;

/**
 * Shared among Report\Debt and Odr\Debt
 */
trait DebtTrait
{
    public function setAmountAndDetails($amount, $details)
    {
        $this->setAmount($amount);

        // reset details if amount is not given, or if more details are not expected
        if (empty($amount) || !$this->getHasMoreDetails()) {
            $details = null;
        }

        $this->setMoreDetails($details);
    }
}
