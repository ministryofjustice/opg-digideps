<?php

namespace AppBundle\Entity\Ndr\Traits;

use AppBundle\Entity\Ndr\Expense;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportDeputyExpenseTrait
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"ndr-expenses-paid-anything"})
     * @Assert\NotBlank(message="ndr.expenses.paidForAnything.notBlank", groups={"ndr-expenses-paid-anything"})
     */
    private $paidForAnything;

    /**
     * @JMS\Type("array<AppBundle\Entity\Ndr\Expense>")
     * @JMS\Groups({"ndr-expenses"})
     *
     * @var Expense[]
     */
    private $expenses = [];

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
     * @return NdrExpensesTrait
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
     * @param Expense $expense
     *
     * @return NdrExpensesTrait
     */
    public function addExpense(Expense $expense)
    {
        $this->expenses[] = $expense;

        return $this;
    }

    /**
     * Get expenses total value.
     *
     * @return float
     */
    public function getExpensesTotalValue()
    {
        $ret = 0;
        foreach ($this->getExpenses() as $expense) {
            $ret += $expense->getAmount();
        }

        return $ret;
    }
}
