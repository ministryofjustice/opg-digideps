<?php

namespace AppBundle\Entity\Ndr\Traits;

use AppBundle\Entity\Ndr\Expense;
use AppBundle\Entity\Ndr\Ndr;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait ExpensesTrait
{
    /**
     * @var string yes|no|null
     *
     * @JMS\Type("string")
     * @JMS\Groups({"ndr-expenses"})
     * @ORM\Column(name="paid_for_anything", type="string", length=3, nullable=true)
     */
    private $paidForAnything;

    /**
     * @JMS\Type("array<AppBundle\Entity\Ndr\Expense>")
     * @JMS\Groups({"ndr-expenses"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Ndr\Expense", mappedBy="odr", cascade={"persist"})
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
     * @return Ndr
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
     * @return Ndr
     */
    public function setExpenses($expenses)
    {
        $this->expenses = $expenses;

        return $this;
    }

    /**
     * @param Expense $expense
     *
     * @return Ndr
     */
    public function addExpense(Expense $expense)
    {
        if (!$this->expenses->contains($expense)) {
            $this->expenses->add($expense);
        }

        return $this;
    }
}
