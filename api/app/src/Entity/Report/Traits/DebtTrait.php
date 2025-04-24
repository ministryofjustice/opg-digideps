<?php

namespace App\Entity\Report\Traits;

use App\Entity\Report\Debt;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait DebtTrait
{
    /**
     * @var Debt[]
     *
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Report\Debt", mappedBy="report", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    #[JMS\Groups(['debt'])]
    private $debts;

    /**
     * @var string yes|no|null
     *
     *
     *
     * @ORM\Column(name="has_debts", type="string", length=5, nullable=true)
     *
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['debt'])]
    private $hasDebts;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column( name="debt_management", type="text", nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['debt-management'])]
    private $debtManagement;

    /**
     * @param mixed $debts
     */
    public function setDebts($debts)
    {
        $this->debts = $debts;
    }

    /**
     * @return Debt[]
     */
    public function getDebts()
    {
        return $this->debts;
    }

    /**
     * @return string
     */
    public function getDebtManagement()
    {
        return $this->debtManagement;
    }

    /**
     * @param string $debtManagement
     */
    public function setDebtManagement($debtManagement)
    {
        $this->debtManagement = $debtManagement;
    }

    /**
     * @param string $typeId
     *
     * @return Debt
     */
    public function getDebtByTypeId($typeId)
    {
        return $this->getDebts()->filter(function (Debt $debt) use ($typeId) {
            return $debt->getDebtTypeId() == $typeId;
        })->first();
    }

    public function addDebt(Debt $debt)
    {
        if (!$this->debts->contains($debt)) {
            $this->debts->add($debt);
        }

        return $this;
    }

    /**
     * Get debts total value.
     *
     *
     *
     *
     *
     * @return float
     */
    #[JMS\VirtualProperty]
    #[JMS\Type('string')]
    #[JMS\SerializedName('debts_total_amount')]
    #[JMS\Groups(['debt'])]
    public function getDebtsTotalAmount()
    {
        $ret = 0;
        foreach ($this->getDebts() as $debt) {
            $ret += $debt->getAmount();
        }

        return $ret;
    }

    /**
     * @return mixed
     */
    public function getHasDebts()
    {
        return $this->hasDebts;
    }

    /**
     * @param mixed $hasDebts
     */
    public function setHasDebts($hasDebts)
    {
        $this->hasDebts = $hasDebts;
    }

    /**
     * @return Debt[]
     */
    public function getDebtsWithValidAmount()
    {
        $debtsWithAValidAmount = $this->getDebts()->filter(function ($debt) {
            return !empty($debt->getAmount());
        });

        return $debtsWithAValidAmount;
    }
}
