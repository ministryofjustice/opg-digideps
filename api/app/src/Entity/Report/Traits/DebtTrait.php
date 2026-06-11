<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use Doctrine\Common\Collections\Collection;
use OPG\Digideps\Backend\Entity\Report\Debt;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait DebtTrait
{
    /**
     * @var Collection<int, Debt>
     */
    #[JMS\Groups(['debt'])]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: Debt::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $debts;

    /**
     * yes|no|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['debt'])]
    #[ORM\Column(name: 'has_debts', type: 'string', length: 5, nullable: true)]
    private ?string $hasDebts = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['debt-management'])]
    #[ORM\Column(name: 'debt_management', type: 'text', nullable: true)]
    private ?string $debtManagement = null;

    /**
     * @param Collection<int, Debt> $debts
     */
    public function setDebts(Collection $debts): void
    {
        $this->debts = $debts;
    }

    /**
     * @return Collection<int, Debt>
     */
    public function getDebts(): Collection
    {
        return $this->debts;
    }

    public function getDebtManagement(): ?string
    {
        return $this->debtManagement;
    }

    public function setDebtManagement(?string $debtManagement): void
    {
        $this->debtManagement = $debtManagement;
    }

    public function getDebtByTypeId(string $typeId): ?Debt
    {
        return $this->getDebts()->filter(function (Debt $debt) use ($typeId): bool {
            return $debt->getDebtTypeId() === $typeId;
        })->first() ?: null;
    }

    public function addDebt(Debt $debt): static
    {
        if (!$this->debts->contains($debt)) {
            $this->debts->add($debt);
        }

        return $this;
    }

    #[JMS\VirtualProperty]
    #[JMS\Type('string')]
    #[JMS\SerializedName('debts_total_amount')]
    #[JMS\Groups(['debt'])]
    public function getDebtsTotalAmount(): float
    {
        $ret = 0.0;
        foreach ($this->getDebts() as $debt) {
            $ret += (float)$debt->getAmount();
        }

        return $ret;
    }

    public function getHasDebts(): ?string
    {
        return $this->hasDebts;
    }

    public function setHasDebts(?string $hasDebts): void
    {
        $this->hasDebts = $hasDebts;
    }

    /**
     * @return Collection<int, Debt>
     */
    public function getDebtsWithValidAmount(): Collection
    {
        return $this->getDebts()->filter(function ($debt): bool {
            return !empty($debt->getAmount());
        });
    }
}
