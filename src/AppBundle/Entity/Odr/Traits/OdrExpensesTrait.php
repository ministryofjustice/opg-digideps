<?php

namespace AppBundle\Entity\Odr\Traits;

use AppBundle\Entity\Odr\Expense;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait OdrExpensesTrait
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-expenses"})
     * @Assert\NotBlank(message="odr.expenses.paidForAnything.notBlank", groups={"odr-expenses"})
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
     * @return mixed
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
}
