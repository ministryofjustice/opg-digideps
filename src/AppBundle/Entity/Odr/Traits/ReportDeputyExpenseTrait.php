<?php

namespace AppBundle\Entity\Odr\Traits;

use AppBundle\Entity\Odr\Expense;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportDeputyExpenseTrait
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-expenses-paid-anything"})
     * @Assert\NotBlank(message="odr.expenses.paidForAnything.notBlank", groups={"odr-expenses-paid-anything"})
     */
    private $paidForAnything;

    /**
     * @JMS\Type("array<AppBundle\Entity\Odr\Expense>")
     * @JMS\Groups({"odr-expenses"})
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
     * @return OdrExpensesTrait
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
