<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\Expense;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait DeputyExpenseTrait
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"expenses-paid-anything"})
     * @Assert\NotBlank(message="odr.expenses.paidForAnything.notBlank", groups={"expenses-paid-anything"})
     */
    private $paidForAnything;

    /**
     * @JMS\Type("array<AppBundle\Entity\Report\Expense>")
     * @JMS\Groups({"expenses"})
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
     * @return OdrExpensesTrait
     */
    public function setExpenses($expenses)
    {
        $this->expenses = $expenses;

        return $this;
    }

    /**
     * @param Expense $expense
     *
     * @return OdrExpensesTrait
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
