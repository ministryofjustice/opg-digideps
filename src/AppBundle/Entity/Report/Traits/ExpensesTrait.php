<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\Expense;
use AppBundle\Entity\Report\Report;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;
use Doctrine\ORM\Mapping as ORM;

trait ExpensesTrait
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"expenses"})
     * @ORM\Column(name="paid_for_anything", type="string", length=3, nullable=true)
     */
    private $paidForAnything;

    /**
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
}
