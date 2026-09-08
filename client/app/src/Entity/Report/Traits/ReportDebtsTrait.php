<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Report\Debt;
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
     * 'yes'|'no'|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['debt'])]
    #[Assert\NotBlank(message: 'report.hasDebts.notBlank', groups: ['debts'])]
    private ?string $hasDebts = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['debt-management'])]
    #[Assert\NotBlank(message: 'report.debts-management.notBlank', groups: ['debt-management'])]
    private ?string $debtManagement = null;

    #[JMS\Type('double')]
    #[JMS\Groups(['debt'])]
    private float $debtsTotalAmount = 0.0;

    public function getDebtsTotalValue(): float
    {
        $ret = 0.0;
        foreach ($this->getDebts() as $debt) {
            $ret += (float) $debt->getAmount();
        }
        return $ret;
    }

    public function getDebtById(string $debtId): ?Debt
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

    public function getDebtsTotalAmount(): float
    {
        return $this->debtsTotalAmount;
    }

    public function setDebtsTotalAmount(float $debtsTotalAmount): static
    {
        $this->debtsTotalAmount = $debtsTotalAmount;

        return $this;
    }

    public function getHasDebts(): ?string
    {
        return $this->hasDebts;
    }

    public function setHasDebts(string $hasDebts): static
    {
        $this->hasDebts = $hasDebts;

        return $this;
    }

    /**
     * Get debt management text.
     */
    public function getDebtManagement(): ?string
    {
        return $this->debtManagement;
    }

    /**
     * Set debt management text.
     */
    public function setDebtManagement(?string $debtManagement): static
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
