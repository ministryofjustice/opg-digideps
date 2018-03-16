<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\Report;
use JMS\Serializer\Annotation as JMS;

trait ReportBalanceTrait
{

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"balance", "balance_mismatch_explanation"})
     * @Assert\NotBlank(message="report.balanceMismatchExplanation.notBlank", groups={"balance"})
     * @Assert\Length( min=10, minMessage="report.balanceMismatchExplanation.length", groups={"balance"})
     *
     * @var string
     */
    private $balanceMismatchExplanation;

    /**
     * @JMS\Type("double")
     *
     * @var float
     */
    private $totalsOffset;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $totalsMatch;

    /**
     * @return string
     */
    public function getBalanceMismatchExplanation()
    {
        return $this->balanceMismatchExplanation;
    }

    /**
     * @param string $balanceMismatchExplanation
     *
     * @return Report
     */
    public function setBalanceMismatchExplanation($balanceMismatchExplanation)
    {
        $this->balanceMismatchExplanation = $balanceMismatchExplanation;
    }

    /**
     * @JMS\Type("double")
     *
     * @var float
     */
    private $calculatedBalance;

    /**
     * @return float
     */
    public function getCalculatedBalance()
    {
        return $this->calculatedBalance;
    }

    /**
     * @param float $calculatedBalance
     *
     * @return Report
     */
    public function setCalculatedBalance($calculatedBalance)
    {
        $this->calculatedBalance = $calculatedBalance;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalsOffset()
    {
        return $this->totalsOffset;
    }

    /**
     * @param float $totalsOffset
     *
     * @return Report
     */
    public function setTotalsOffset($totalsOffset)
    {
        $this->totalsOffset = $totalsOffset;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTotalsMatch()
    {
        return $this->totalsMatch;
    }

    /**
     * @param bool $totalsMatch
     */
    public function setTotalsMatch($totalsMatch)
    {
        $this->totalsMatch = $totalsMatch;
    }

    public function shouldShowBalanceWarning()
    {
        // if not due dont show warning
        if (!$this->isDue()) {
            return false;
        }

        // if accounts not started don't show warning
        if ($this->getStatus()->getBankAccountsState()['state'] == Status::STATE_NOT_STARTED) {
            return false;
        }

        switch ($this->getType()) {
            case Report::TYPE_102:
            case Report::TYPE_102_4:
                // if a money section not started, dont show warning
                if ($this->getStatus()->getMoneyInState()['state'] == Status::STATE_NOT_STARTED ||
                    $this->getStatus()->getMoneyOutState()['state'] == Status::STATE_NOT_STARTED
                ) {
                    return false;
                }
                break;
            default:
                return false;
        }

        return true;
    }
}
