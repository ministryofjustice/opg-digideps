<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\Debt;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait DebtTrait
{

    /**
     * @var Debt[]
     *
     * @JMS\Groups({"debt"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\Debt", mappedBy="report", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $debts;


    /**
     * @var string yes|no|null
     *
     * @JMS\Type("string")
     * @JMS\Groups({"debt"})
     *
     * @ORM\Column(name="has_debts", type="string", length=5, nullable=true)
     *
     * @var string
     */
    private $hasDebts;


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

    /**
     * @param Debt $debt
     */
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
     * @JMS\VirtualProperty
     * @JMS\Type("string")
     * @JMS\SerializedName("debts_total_amount")
     * @JMS\Groups({"debt"})
     *
     * @return float
     */
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
