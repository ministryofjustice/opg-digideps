<?php

namespace AppBundle\Entity\Odr\Traits;

use AppBundle\Entity\Client;
use AppBundle\Entity\Odr\Expense;
use AppBundle\Entity\Odr\IncomeBenefit;
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
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-expenses"})
     * @Assert\NotBlank(message="odr.expenses.planningToClaimExpenses.notBlank", groups={"odr-expenses"})
     */
    private $planningToClaimExpenses;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-expenses"})
     * @Assert\NotBlank(message="odr.expenses.planningToClaimExpensesDetails.notBlank", groups={"odr-expenses-planning-claim-yes"})
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
     * @return mixed
     */
    public function getExpenses()
    {
        return $this->expenses;
    }

    /**
     * @param mixed $expenses
     * @return OdrExpensesTrait
     */
    public function setExpenses($expenses)
    {
        $this->expenses = $expenses;
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
