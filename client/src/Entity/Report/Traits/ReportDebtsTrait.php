<?php

namespace App\Entity\Report\Traits;

use App\Entity\Report\Debt;
use App\Entity\Report\Report;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait ReportDebtsTrait
{
    /**
     * @JMS\Type("array<App\Entity\Report\Debt>")
     * @JMS\Groups({"debt"})
     *
     * @var Debt[]
     */
    private $debts = [];

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"debt"})
     *
     * @Assert\NotBlank(message="report.hasDebts.notBlank", groups={"debts"})
     *
     * @var string
     */
    private $hasDebts;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"debt-management"})
     *
     * @Assert\NotBlank(message="report.debts-management.notBlank", groups={"debt-management"})
     *
     * @var string
     */
    private $debtManagement;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"debt"})
     *
     * @var decimal
     */
    private $debtsTotalAmount;

    /**
     * Get debts total value.
     *
     * @return float
     */
    public function getDebtsTotalValue()
    {
        $ret = 0;
        foreach ($this->getDebts() as $debt) {
            $ret += $debt->getAmount();
        }

        return $ret;
    }

    /**
     * @param $debtId
     *
     * @return Debt|null
     */
    public function getDebtById($debtId)
    {
        foreach ($this->getDebts() as $debt) {
            if ($debt->getDebtTypeId() == $debtId) {
                return $debt;
            }
        }

        return null;
    }

    /**
     * @return Debt[]
     */
    public function getDebts()
    {
        return $this->debts;
    }

    /**
     * @param Debt[] $debts
     *
     * @return Report
     */
    public function setDebts($debts)
    {
        $this->debts = $debts;

        return $this;
    }

    /**
     * @return decimal
     */
    public function getDebtsTotalAmount()
    {
        return $this->debtsTotalAmount;
    }

    /**
     * @param decimal $debtsTotalAmount
     */
    public function setDebtsTotalAmount($debtsTotalAmount)
    {
        $this->debtsTotalAmount = $debtsTotalAmount;

        return $this;
    }

    /**
     * @return string
     */
    public function getHasDebts()
    {
        return $this->hasDebts;
    }

    /**
     * @param $hasDebts bool
     *
     * @return Report
     */
    public function setHasDebts($hasDebts)
    {
        $this->hasDebts = $hasDebts;

        return $this;
    }

    /**
     * Get debt management text.
     *
     * @return string
     */
    public function getDebtManagement()
    {
        return $this->debtManagement;
    }

    /**
     * Set debt management text.
     *
     * @param string $debtManagement
     *
     * @return $this
     */
    public function setDebtManagement($debtManagement)
    {
        $this->debtManagement = $debtManagement;

        return $this;
    }

    public function debtsValid(ExecutionContextInterface $context)
    {
        if ('yes' == $this->getHasDebts() && 0 === count($this->getDebtsWithValidAmount())) {
            $context->addViolation('report.hasDebts.mustHaveAtLeastOneDebt');
        }
    }

    /**
     * @return Debt[]
     */
    public function getDebtsWithValidAmount()
    {
        return array_filter($this->debts, function ($debt) {
            return !empty($debt->getAmount());
        });
    }
}
