<?php

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use OPG\Digideps\Frontend\Entity\Report\Debt;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait ReportDebtsTrait
{
    /**
     * @var Debt[]
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\Debt>')]
    #[JMS\Groups(['debt'])]
    private array $debts = [];

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['debt'])]
    #[Assert\NotBlank(message: 'report.hasDebts.notBlank', groups: ['debts'])]
    private $hasDebts;

    /**
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
    public function getDebtsTotalValue()
    {
        $ret = 0;
        foreach ($this->getDebts() as $debt) {
            $ret += $debt->getAmount();
        }

        return $ret;
    }

    public function getDebtById($debtId): ?Debt
    {
        return array_find($this->getDebts(), fn ($debt) => $debt->getDebtTypeId() == $debtId);
    }

    /**
     * @return Debt[]
     */
    public function getDebts(): array
    {
        return $this->debts;
    }

    /**
     * @param Debt[] $debts
     */
    public function setDebts(array $debts): static
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
    public function setDebtsTotalAmount($debtsTotalAmount): static
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
     */
    public function setHasDebts($hasDebts): static
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
     */
    public function setDebtManagement($debtManagement): static
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
    public function getDebtsWithValidAmount(): array
    {
        return array_filter($this->debts, function ($debt): bool {
            return !empty($debt->getAmount());
        });
    }
}
