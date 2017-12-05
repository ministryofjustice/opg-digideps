<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\Fee;
use AppBundle\Entity\Report\Expense;
use AppBundle\Entity\Report\Report;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;


trait FeeExpensesTrait
{
    /**
     * @var Fee[]
     *
     * @JMS\Groups({"fee"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\Fee", mappedBy="report", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $fees;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"fee"})
     * @ORM\Column(name="reason_for_no_fees", type="text", nullable=true)
     */
    private $reasonForNoFees;


    /**
     * @var string yes|no|null
     *
     * @JMS\Type("string")
     * @JMS\Groups({"expenses"})
     * @ORM\Column(name="paid_for_anything", type="string", length=3, nullable=true)
     */
    private $paidForAnything;

    /**
     * Used for both
     * - Lay deputy expenses
     * - PA Fees outside practice direction
     *
     * @var Expense[]
     *
     * @JMS\Type("array<AppBundle\Entity\Report\Expense>")
     * @JMS\Groups({"expenses"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\Expense", mappedBy="report", cascade={"persist", "remove"})
     *
     * @var Expense[]
     */
    private $expenses;


    /**
     * @return Fee[]
     */
    public function getFees()
    {
        return $this->fees;
    }

    /**
     * @param Fee $fee
     */
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
     * @JMS\VirtualProperty
     * @JMS\Type("double")
     * @JMS\SerializedName("fees_total")
     * @JMS\Groups({"fee"})
     *
     * @return float
     */
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
     * Alternative to have a column
     *
     * @JMS\VirtualProperty
     * @JMS\Type("string")
     * @JMS\SerializedName("has_fees")
     * @JMS\Groups({"fee"})
     *
     * @return float
     */
    public function getHasFees()
    {
        // never set -> return null
        if (0 === count($this->getFeesWithValidAmount()) && $this->getReasonForNoFees() === null) {
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
     * @param Expense $expense
     *
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
     * @JMS\VirtualProperty
     * @JMS\Type("double")
     * @JMS\SerializedName("expenses_total")
     * @JMS\Groups({"expenses"})
     *
     * @return float
     */
    public function getExpensesTotal()
    {
        $ret = 0;
        foreach ($this->getExpenses() as $record) {
            $ret += $record->getAmount();
        }

        return $ret;
    }

    /**
     * //TODO unit test
     * @return bool
     */
    public function expensesSectionCompleted()
    {
        return count($this->getExpenses()) > 0 || $this->getPaidForAnything() === 'no';
    }

    /**
     * //TODO unit test
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
     * //TODO unit test
     * @return bool
     */
    public function paFeesExpensesCompleted()
    {
        $countValidFees = count($this->getFeesWithValidAmount());
        $countExpenses = count($this->getExpenses());

        $feeComplete = $countValidFees || !empty($this->getReasonForNoFees());
        $expenseComplete = $this->getPaidForAnything() === 'no'
            || ($this->getPaidForAnything() === 'yes' && count($countExpenses));

        return $feeComplete && $expenseComplete;
    }

}
