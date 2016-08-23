<?php

namespace AppBundle\Entity\Traits;

use AppBundle\Entity\Odr\Expense;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;
use Doctrine\ORM\Mapping as ORM;

trait OdrExpensesTrait
{

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-expenses"})
     * @ORM\Column(name="paid_for_anything", type="string", length=3, nullable=true)
     */
    private $paidForAnything;

    /**
     * @JMS\Type("array<AppBundle\Entity\Odr\Expense>")
     * @JMS\Groups({"odr-expenses"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Odr\Expense", mappedBy="odr", cascade={"persist"})
     *
     * @var ArrayCollection
     */
    private $expenses;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-expenses"})
     * @ORM\Column(name="planning_claim_expenses", type="string", length=3, nullable=true)
     */
    private $planningToClaimExpenses;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-expenses"})
     * @ORM\Column(name="planning_claim_expenses_details", type="text", nullable=true)
     */
    private $planningToClaimExpensesDetails;

    /**
     * @return string
     */
    public function getPaidForAnything()
    {
        return $this->paidForAnything;
    }

    /**
     * @param string $paidForAnything
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
     * @param Expense[]|null $expenses
     * @return OdrExpensesTrait
     */
    public function setExpenses($expenses)
    {
        $this->expenses = $expenses;
        return $this;
    }

    /**
     * @param Expense $expense
     * @return OdrExpensesTrait
     */
    public function addExpense(Expense $expense)
    {
        if (!$this->expenses->contains($expense)) {
            $this->expenses->add($expense);
        }

        return $this;
    }


    /**
     * @return string
     */
    public function getPlanningToClaimExpenses()
    {
        return $this->planningToClaimExpenses;
    }

    /**
     * @param string $planningToClaimExpenses
     * @return OdrExpensesTrait
     */
    public function setPlanningToClaimExpenses($planningToClaimExpenses)
    {
        $this->planningToClaimExpenses = $planningToClaimExpenses;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlanningToClaimExpensesDetails()
    {
        return $this->planningToClaimExpensesDetails;
    }

    /**
     * @param string $planningToClaimExpensesDetails
     * @return OdrExpensesTrait
     */
    public function setPlanningToClaimExpensesDetails($planningToClaimExpensesDetails)
    {
        $this->planningToClaimExpensesDetails = $planningToClaimExpensesDetails;
        return $this;
    }



}
