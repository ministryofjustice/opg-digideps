<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Report\Expense;
use OPG\Digideps\Backend\Entity\Report\Fee;
use OPG\Digideps\Backend\Entity\Report\Report;

trait FeeExpensesTrait
{
    /**
     * @var Collection<int, Fee>
     */
    #[JMS\Groups(['fee'])]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: Fee::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $fees;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['fee'])]
    #[ORM\Column(name: 'reason_for_no_fees', type: 'text', nullable: true)]
    private $reasonForNoFees;

    /**
     * @var string yes|no|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['expenses'])]
    #[ORM\Column(name: 'paid_for_anything', type: 'string', length: 3, nullable: true)]
    private $paidForAnything;

    /**
     * Used for both
     * - Lay deputy expenses
     * - PA Fees outside practice direction.
     *
     * @var ?Collection<int, Expense>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\Expense>')]
    #[JMS\Groups(['expenses'])]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: Expense::class, cascade: ['persist', 'remove'])]
    private $expenses;

    /**
     * @return Collection<int, Fee>
     */
    public function getFees()
    {
        return $this->fees;
    }

    public function addFee(Fee $fee)
    {
        if (!$this->fees->contains($fee)) {
            $this->fees->add($fee);
        }

        return $this;
    }

    /**
     * @param string $typeId
     *
     * @return ?Fee
     */
    public function getFeeByTypeId($typeId)
    {
        return $this->getFees()->filter(function (Fee $fee) use ($typeId) {
            return $fee->getFeeTypeId() === $typeId;
        })->first() ?: null;
    }

    /**
     * @return string
     */
    public function getReasonForNoFees()
    {
        return $this->reasonForNoFees;
    }

    /**
     * @param string $reasonForNoFees
     */
    public function setReasonForNoFees($reasonForNoFees)
    {
        $this->reasonForNoFees = $reasonForNoFees;
    }

    /**
     * Get fee total value.
     *
     * @return float
     */
    #[JMS\VirtualProperty]
    #[JMS\Type('double')]
    #[JMS\SerializedName('fees_total')]
    #[JMS\Groups(['fee'])]
    public function getFeesTotal()
    {
        $ret = 0;
        foreach ($this->getFees() as $fee) {
            $ret += $fee->getAmount();
        }

        return $ret;
    }

    /**
     * @return Collection<int, Fee>
     */
    public function getFeesWithValidAmount()
    {
        $fees = $this->getFees()->filter(function ($fee) {
            return !empty($fee->getAmount());
        });

        return $fees;
    }

    /**
     * Implement the report.hasFees based on the content of fees and reaons for no fees
     * Alternative to have a column.
     *
     * @return float
     */
    #[JMS\VirtualProperty]
    #[JMS\Type('string')]
    #[JMS\SerializedName('has_fees')]
    #[JMS\Groups(['fee'])]
    public function getHasFees()
    {
        // never set -> return null
        if (0 === count($this->getFeesWithValidAmount()) && null === $this->getReasonForNoFees()) {
            return null;
        }

        return $this->getReasonForNoFees() ? 'no' : 'yes';
    }

    public function getPaidForAnything(): ?string
    {
        return $this->paidForAnything;
    }

    /**
     * @param string $paidForAnything
     *
     * @return Report
     */
    public function setPaidForAnything($paidForAnything)
    {
        $this->paidForAnything = $paidForAnything;

        return $this;
    }

    /**
     * @return Collection<int, Expense>
     */
    public function getExpenses()
    {
        return $this->expenses ?? new ArrayCollection();
    }

    /**
     * @param ?Collection<int, Expense> $expenses
     *
     * @return Report
     */
    public function setExpenses($expenses)
    {
        $this->expenses = $expenses;

        return $this;
    }

    /**
     * @return Report
     */
    public function addExpense(Expense $expense)
    {
        if ($this->expenses !== null && !$this->expenses->contains($expense)) {
            $this->expenses->add($expense);
        }

        return $this;
    }

    /**
     * @return float
     */
    #[JMS\VirtualProperty]
    #[JMS\Type('double')]
    #[JMS\SerializedName('expenses_total')]
    #[JMS\Groups(['expenses'])]
    public function getExpensesTotal()
    {
        $ret = 0;
        foreach ($this->getExpenses() as $record) {
            $ret += (float) $record->getAmount();
        }

        return $ret;
    }

    /**
     * //TODO unit test.
     *
     * @return bool
     */
    public function expensesSectionCompleted()
    {
        return count($this->getExpenses()) > 0 || 'no' === $this->getPaidForAnything();
    }

    /**
     * //TODO unit test.
     *
     * @return bool
     */
    public function paFeesExpensesNotStarted()
    {
        return 0 === count($this->getFeesWithValidAmount())
            && empty($this->getReasonForNoFees())
            && 0 === count($this->getExpenses())
            && empty($this->getPaidForAnything());
    }

    /**
     * //TODO unit test.
     *
     * @return bool
     */
    public function paFeesExpensesCompleted()
    {
        $countValidFees = count($this->getFeesWithValidAmount());
        $countExpenses = count($this->getExpenses());

        $feeComplete = $countValidFees || !empty($this->getReasonForNoFees());
        $expenseComplete = 'no' === $this->getPaidForAnything()
            || ('yes' === $this->getPaidForAnything() && $countExpenses);

        return $feeComplete && $expenseComplete;
    }
}
