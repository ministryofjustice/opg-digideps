<?php

namespace App\Entity\Ndr\Traits;

use App\Entity\Ndr\Expense;
use App\Entity\Ndr\Ndr;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait ExpensesTrait
{
    /**
     * @var string yes|no|null
     *
     *
     *
     * @ORM\Column(name="paid_for_anything", type="string", length=3, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['ndr-expenses'])]
    private $paidForAnything;

    /**
     *
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Ndr\Expense", mappedBy="ndr", cascade={"persist"})
     *
     * @var Expense[]
     */
    #[JMS\Type('ArrayCollection<App\Entity\Ndr\Expense>')]
    #[JMS\Groups(['ndr-expenses'])]
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
