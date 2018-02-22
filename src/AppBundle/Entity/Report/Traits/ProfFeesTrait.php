<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\Expense;
use AppBundle\Entity\Report\Fee;
use AppBundle\Entity\Report\Report;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait ProfFeesTrait
{
    /**
     * //TODO unit test
     *
     * @return bool
     */
    public function profCurrentFeesSectionCompleted()
    {
        return count($this->getExpenses()) > 0 || $this->getPaidForAnything() === 'no';
    }

    /**
     * //TODO unit test
     *
     * @return bool
     */
    public function profCurrentFeesNotStarted()
    {
        return true;
//        return 0 === count($this->getProfCurrentFeesWithValidAmount())
//            && empty($this->getReasonForNoCurrentFees())
//            && 0 === count($this->getExpenses())
//            && empty($this->getPaidForAnything());
    }

    /**
     * //TODO unit test
     *
     * @return bool
     */
    public function profCurrentFeesCompleted()
    {
        return false;
//        $countValidFees = count($this->getFeesWithValidAmount());
//        $countExpenses = count($this->getExpenses());
//
//        $feeComplete = $countValidFees || !empty($this->getReasonForNoFees());
//        $expenseComplete = $this->getPaidForAnything() === 'no'
//            || ($this->getPaidForAnything() === 'yes' && count($countExpenses));
//
//        return $feeComplete && $expenseComplete;
    }
}
