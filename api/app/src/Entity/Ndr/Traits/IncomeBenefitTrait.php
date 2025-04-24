<?php

namespace App\Entity\Ndr\Traits;

use App\Entity\Ndr\OneOff;
use App\Entity\Ndr\StateBenefit;
use JMS\Serializer\Annotation as JMS;

trait IncomeBenefitTrait
{
    /**
     * @var StateBenefit[]
     *
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Ndr\StateBenefit", mappedBy="ndr")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    #[JMS\Groups(['state-benefits'])]
    private $stateBenefits;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="receive_state_pension", type="text", nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['pension'])]
    private $receiveStatePension;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="receive_other_income", type="text", nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['pension'])]
    private $receiveOtherIncome;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="receive_other_income_details", type="text", nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['pension'])]
    private $receiveOtherIncomeDetails;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="expect_compensation_damages", type="text", nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['damages'])]
    private $expectCompensationDamages;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="expect_compensation_damages_details", type="text", nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['damages'])]
    private $expectCompensationDamagesDetails;

    /**
     * @var OneOff[]
     *
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Ndr\OneOff", mappedBy="ndr")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    #[JMS\Groups(['one-off'])]
    private $oneOff;

    /**
     * @return StateBenefit[]
     */
    public function getStateBenefits()
    {
        return $this->stateBenefits;
    }

    /**
     * @param IncomeBenefit[] $stateBenefits
     *
     * @return NdrIncomeBenefitTrait
     */
    public function addStateBenefits($stateBenefits)
    {
        if (!$this->stateBenefits->contains($stateBenefits)) {
            $this->stateBenefits->add($stateBenefits);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getReceiveOtherIncomeDetails()
    {
        return $this->receiveOtherIncomeDetails;
    }

    /**
     * @param string $receiveOtherIncomeDetails
     *
     * @return NdrIncomeBenefitTrait
     */
    public function setReceiveOtherIncomeDetails($receiveOtherIncomeDetails)
    {
        $this->receiveOtherIncomeDetails = $receiveOtherIncomeDetails;

        return $this;
    }

    /**
     * @return string
     */
    public function getExpectCompensationDamages()
    {
        return $this->expectCompensationDamages;
    }

    /**
     * @param string $expectCompensationDamages
     *
     * @return NdrIncomeBenefitTrait
     */
    public function setExpectCompensationDamages($expectCompensationDamages)
    {
        $this->expectCompensationDamages = $expectCompensationDamages;

        return $this;
    }

    /**
     * @return string
     */
    public function getExpectCompensationDamagesDetails()
    {
        return $this->expectCompensationDamagesDetails;
    }

    /**
     * @param string $expectCompensationDamagesDetails
     *
     * @return NdrIncomeBenefitTrait
     */
    public function setExpectCompensationDamagesDetails($expectCompensationDamagesDetails)
    {
        $this->expectCompensationDamagesDetails = $expectCompensationDamagesDetails;

        return $this;
    }

    /**
     * @return string
     */
    public function getReceiveStatePension()
    {
        return $this->receiveStatePension;
    }

    /**
     * @param string $receiveStatePension
     *
     * @return NdrIncomeBenefitTrait
     */
    public function setReceiveStatePension($receiveStatePension)
    {
        $this->receiveStatePension = $receiveStatePension;

        return $this;
    }

    /**
     * @return string
     */
    public function getReceiveOtherIncome()
    {
        return $this->receiveOtherIncome;
    }

    /**
     * @param string $receiveOtherIncome
     *
     * @return NdrIncomeBenefitTrait
     */
    public function setReceiveOtherIncome($receiveOtherIncome)
    {
        $this->receiveOtherIncome = $receiveOtherIncome;

        return $this;
    }

    /**
     * @return OneOff[]
     */
    public function getOneOff()
    {
        return $this->oneOff;
    }

    /**
     * @param IncomeBenefit[] $oneOff
     *
     * @return NdrIncomeBenefitTrait
     */
    public function addOneOff($oneOff)
    {
        if (!$this->oneOff->contains($oneOff)) {
            $this->oneOff->add($oneOff);
        }

        return $this;
    }

    /**
     * @param string $typeId
     *
     * @return OneOff
     */
    public function getOneOffByTypeId($typeId)
    {
        return $this->getOneOff()->filter(function (OneOff $incomeOneOff) use ($typeId) {
            return $incomeOneOff->getTypeId() == $typeId;
        })->first();
    }

    /**
     * @param string $typeId
     *
     * @return StateBenefit
     */
    public function getStateBenefitByTypeId($typeId)
    {
        return $this->getStateBenefits()->filter(function (StateBenefit $sb) use ($typeId) {
            return $sb->getTypeId() == $typeId;
        })->first();
    }
}
