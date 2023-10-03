<?php

namespace App\Entity\Report\Traits;

use App\Entity\Report\Expense;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportDeputyExpenseTrait
{
    /**
     * @var string yes/no
     *
     * @JMS\Type("string")
     * @JMS\Groups({"expenses-paid-anything"})
     * @Assert\NotBlank(message="ndr.expenses.paidForAnything.notBlank", groups={"expenses-paid-anything"})
     */
    private $paidForAnything;

    /**
     * @JMS\Type("array<App\Entity\Report\Expense>")
     * @JMS\Groups({"expenses"})
     *
     * @var Expense[]
     */
    private $expenses = [];

    /**
     * @JMS\Type("double")
     * @JMS\Groups({"expenses-total"})
     */
    private $expensesTotal;

    /**
     * @return string
     */
    public function getPaidForAnything()
    {
        return $this->paidForAnything;
    }

    /**
     * @param string $paidForAnything
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
     * @param mixed $expenses
     *
     * @return NdrExpensesTrait
     */
    public function setExpenses($expenses)
    {
        $this->expenses = $expenses;

        return $this;
    }

    /**
     * @return NdrExpensesTrait
     */
    public function addExpense(Expense $expense)
    {
        $this->expenses[] = $expense;

        return $this;
    }

    /**
     * @return string
     */
    public function getExpensesTotal()
    {
        return $this->expensesTotal;
    }

    /**
     * @param string $expensesTotal
     */
    public function setExpensesTotal($expensesTotal)
    {
        $this->expensesTotal = $expensesTotal;
    }
}
