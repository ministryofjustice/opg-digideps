<?php

namespace AppBundle\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

trait DebtsTrait
{
    /**
     * @JMS\Type("array<AppBundle\Entity\Report\Debt>")
     * @JMS\Groups({"debt"})
     *
     * @var ArrayCollection
     */
    private $debts;

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
     * @param string $hasDebts
     */
    public function setHasDebts($hasDebts)
    {
        $this->hasDebts = $hasDebts;

        return $this;
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function debtsValid(ExecutionContextInterface $context)
    {
        if ($this->getHasDebts() == 'yes' && count($this->getDebtsWithValidAmount()) === 0) {
            $context->addViolation('report.hasDebts.mustHaveAtLeastOneDebt');
        }
    }

    /**
     * @return Debt[]
     */
    public function getDebtsWithValidAmount()
    {
        $debtsWithAValidAmount = array_filter($this->debts, function ($debt) {
            return !empty($debt->getAmount());
        });

        return $debtsWithAValidAmount;
    }
}
