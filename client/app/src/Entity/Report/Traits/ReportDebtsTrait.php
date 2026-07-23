<?php

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use OPG\Digideps\Frontend\Entity\Report\Debt;
use OPG\Digideps\Frontend\Entity\Report\Report;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait ReportDebtsTrait
{
    /**
     *
     * @var Debt[]
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\Debt>')]
    #[JMS\Groups(['debt'])]
    private $debts = [];

    /**
     *
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['debt'])]
    #[Assert\NotBlank(message: 'report.hasDebts.notBlank', groups: ['debts'])]
    private $hasDebts;

    /**
     *
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['debt-management'])]
    #[Assert\NotBlank(message: 'report.debts-management.notBlank', groups: ['debt-management'])]
    private $debtManagement;

    /**
     *
     * @var string $debtsTotalAmount
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['debt'])]
    private $debtsTotalAmount;

    /**
     * Get debts total value.
     *
     * @return float
     */
    public function getDebtsTotalValue(): int|float
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
     * @return string
     */
    public function getDebtsTotalAmount()
    {
        return $this->debtsTotalAmount;
    }

    /**
     * @param string $debtsTotalAmount
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

    public function debtsValid(ExecutionContextInterface $context): void
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
        $debtsWithAValidAmount = array_filter($this->debts, function ($debt): bool {
            return !empty($debt->getAmount());
        });

        return $debtsWithAValidAmount;
    }
}
