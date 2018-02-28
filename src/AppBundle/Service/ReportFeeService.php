<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\ProfServiceFee;

class ReportFeeService
{

    /**
     * Calculate total Received Fees
     *
     * @param array $profFees
     * @return float
     */
    public function getTotalReceivedFees(array $profFees)
    {
        $total = 0.00;

        foreach($profFees as $profFee)
        {
            /**  @var ProfServiceFee $profFee */
            $total += $profFee->getAmountReceived();
        }

        return number_format($total,2, '.', ',');
    }

    /**
     * Calculate total Charged Fees
     *
     * @param array $profFees
     * @return float
     */
    public function getTotalChargedFees(array $profFees)
    {
        $total = 0.00;

        foreach($profFees as $profFee)
        {
            /**  @var ProfServiceFee $profFee */
            $total += $profFee->getAmountCharged();
        }

        return number_format($total,2, '.', ',');
    }
}
