<?php

namespace App\Entity\Report\Traits;

use App\Entity\Report\Expense;
use App\Entity\Report\Fee;
use App\Entity\Report\Report;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait FeeExpensesTrait
{
    /**
     * @var Fee[]
     *
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Report\Fee", mappedBy="report", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    #[JMS\Groups(['fee'])]
    private $fees;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="reason_for_no_fees", type="text", nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['fee'])]
    private $reasonForNoFees;

    /**
     * @var string yes|no|null
     *
     *
     *
     * @ORM\Column(name="paid_for_anything", type="string", length=3, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['expenses'])]
    private $paidForAnything;

    /**
     * Used for both
     * - Lay deputy expenses
     * - PA Fees outside practice direction.
     *
     * @var Expense[]
     *
     *
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Report\Expense", mappedBy="report", cascade={"persist", "remove"})
     *
     * @var Expense[]
     */
    #[JMS\Type('ArrayCollection<App\Entity\Report\Expense>')]
    #[JMS\Groups(['expenses'])]
    private $expenses;

    /**
     * @return Fee[]
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
     * @return Fee
     */
    public function getFeeByTypeId($typeId)
    {
        return $this->getFees()->filter(function (Fee $fee) use ($typeId) {
            return $fee->getFeeTypeId() == $typeId;
        })->first();
    }

    /**
     * @return mixed
     */
    public function getReasonForNoFees()
    {
        return $this->reasonForNoFees;
    }

    /**
     * @param mixed $reasonForNoFees
     */
    public function setReasonForNoFees($reasonForNoFees)
    {
        $this->reasonForNoFees = $reasonForNoFees;
    }

    /**
     * Get fee total value.
     *
     *
     *
     *
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
     * @return Fee[]
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
     *
     *
     *
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

    /**
     * @return string
     */
    public function getPaidForAnything()
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
     * @return Expense[]
     */
    public function getExpenses()
    {
        return $this->expenses;
    }

    /**
     * @param Expense[]|null $expenses
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
        if (!$this->expenses->contains($expense)) {
            $this->expenses->add($expense);
        }

        return $this;
    }

    /**
     *
     *
     *
     *
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
            $ret += $record->getAmount();
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
