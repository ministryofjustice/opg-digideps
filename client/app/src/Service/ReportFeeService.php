<?php

namespace App\Service;

use App\Entity\Report\ProfServiceFee;

class ReportFeeService
{
    /**
     * Calculate total Received Fees.
     *
     * @return float
     */
    public function getTotalReceivedFees(array $profFees)
    {
        $total = 0.00;

        foreach ($profFees as $profFee) {
            /* @var ProfServiceFee $profFee */
            $total += $profFee->getAmountReceived();
        }

        return $total;
    }

    /**
     * Calculate total Charged Fees.
     *
     * @return float
     */
    public function getTotalChargedFees(array $profFees)
    {
        $total = 0.00;

        foreach ($profFees as $profFee) {
            /* @var ProfServiceFee $profFee */
            $total += $profFee->getAmountCharged();
        }

        return $total;
    }
}
