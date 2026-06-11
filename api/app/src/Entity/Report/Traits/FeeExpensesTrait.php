<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Report\Expense;
use OPG\Digideps\Backend\Entity\Report\Fee;

trait FeeExpensesTrait
{
    /**
     * @var Collection<int, Fee>
     */
    #[JMS\Groups(['fee'])]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: Fee::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $fees;

    #[JMS\Type('string')]
    #[JMS\Groups(['fee'])]
    #[ORM\Column(name: 'reason_for_no_fees', type: 'text', nullable: true)]
    private ?string $reasonForNoFees = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['expenses'])]
    #[ORM\Column(name: 'paid_for_anything', type: 'string', length: 3, nullable: true)]
    private ?string $paidForAnything = null;

    /**
     * Used for both
     * - Lay deputy expenses
     * - PA Fees outside practice direction.
     *
     * @var Collection<int, Expense>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\Expense>')]
    #[JMS\Groups(['expenses'])]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: Expense::class, cascade: ['persist', 'remove'])]
    private Collection $expenses;

    /**
     * @return Collection<int, Fee>
     */
    public function getFees(): Collection
    {
        return $this->fees;
    }

    public function addFee(Fee $fee): static
    {
        if (!$this->fees->contains($fee)) {
            $this->fees->add($fee);
        }

        return $this;
    }

    public function getFeeByTypeId(string $typeId): ?Fee
    {
        return $this->getFees()->filter(function (Fee $fee) use ($typeId): bool {
            return $fee->getFeeTypeId() === $typeId;
        })->first() ?: null;
    }

    public function getReasonForNoFees(): ?string
    {
        return $this->reasonForNoFees;
    }

    public function setReasonForNoFees(?string $reasonForNoFees): void
    {
        $this->reasonForNoFees = $reasonForNoFees;
    }

    #[JMS\VirtualProperty]
    #[JMS\Type('double')]
    #[JMS\SerializedName('fees_total')]
    #[JMS\Groups(['fee'])]
    public function getFeesTotal(): float
    {
        $ret = 0.0;
        foreach ($this->getFees() as $fee) {
            $ret += (float)$fee->getAmount();
        }

        return $ret;
    }

    /**
     * @return Collection<int, Fee>
     */
    public function getFeesWithValidAmount(): Collection
    {
        return $this->getFees()->filter(function ($fee): bool {
            return !empty($fee->getAmount());
        });
    }

    /**
     * Implement the report.hasFees based on the content of fees and reasons for no fees
     * Alternative to have a column.
     */
    #[JMS\VirtualProperty]
    #[JMS\Type('string')]
    #[JMS\SerializedName('has_fees')]
    #[JMS\Groups(['fee'])]
    public function getHasFees(): ?string
    {
        // never set -> return null
        if ($this->getFeesWithValidAmount()->count() === 0 && $this->getReasonForNoFees() === null) {
            return null;
        }

        return $this->getReasonForNoFees() ? 'no' : 'yes';
    }

    public function getPaidForAnything(): ?string
    {
        return $this->paidForAnything;
    }

    public function setPaidForAnything(?string $paidForAnything): static
    {
        $this->paidForAnything = $paidForAnything;

        return $this;
    }

    /**
     * @return Collection<int, Expense>
     */
    public function getExpenses(): Collection
    {
        return $this->expenses ?? new ArrayCollection();
    }

    /**
     * @param Collection<int, Expense> $expenses
     */
    public function setExpenses(Collection $expenses): static
    {
        $this->expenses = $expenses;

        return $this;
    }

    public function addExpense(Expense $expense): static
    {
        if (!$this->expenses->contains($expense)) {
            $this->expenses->add($expense);
        }

        return $this;
    }

    #[JMS\VirtualProperty]
    #[JMS\Type('double')]
    #[JMS\SerializedName('expenses_total')]
    #[JMS\Groups(['expenses'])]
    public function getExpensesTotal(): float
    {
        $ret = 0.0;
        foreach ($this->getExpenses() as $record) {
            $ret += (float) $record->getAmount();
        }

        return $ret;
    }

    public function expensesSectionCompleted(): bool
    {
        return count($this->getExpenses()) > 0 || $this->getPaidForAnything() === 'no';
    }

    public function paFeesExpensesNotStarted(): bool
    {
        return count($this->getFeesWithValidAmount()) === 0
            && empty($this->getReasonForNoFees())
            && count($this->getExpenses()) === 0
            && empty($this->getPaidForAnything());
    }

    public function paFeesExpensesCompleted(): bool
    {
        $countValidFees = count($this->getFeesWithValidAmount());
        $countExpenses = count($this->getExpenses());

        $feeComplete = $countValidFees || !empty($this->getReasonForNoFees());
        $expenseComplete = $this->getPaidForAnything() === 'no' || ($this->getPaidForAnything() === 'yes' && $countExpenses);

        return $feeComplete && $expenseComplete;
    }
}
