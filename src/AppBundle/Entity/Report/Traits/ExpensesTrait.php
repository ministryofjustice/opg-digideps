<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\Expense;
use AppBundle\Entity\Report\Report;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait ExpensesTrait
{
    /**
     * @var string yes|no|null
     *
     * @JMS\Type("string")
     * @JMS\Groups({"expenses"})
     * @ORM\Column(name="paid_for_anything", type="string", length=3, nullable=true)
     */
    private $paidForAnything;

    /**
     * @var Expense[]
     *
     * @JMS\Type("array<AppBundle\Entity\Report\Expense>")
     * @JMS\Groups({"expenses"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\Expense", mappedBy="report", cascade={"persist"})
     *
     * @var Expense[]
     */
    private $expenses;

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
     * @return float
     */
    public function getExpensesTotal()
    {
        $ret = 0;
        foreach ($this->getExpenses() as $record) {
            $ret += $record->getAmount();
        }
        if ($this->has106Flag()) {
            foreach ($this->getFees() as $record) {
                $ret += $record->getAmount();
            }
        }

        return $ret;
    }
}
