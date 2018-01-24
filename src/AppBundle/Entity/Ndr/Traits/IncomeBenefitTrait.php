<?php
/**
 * Created by PhpStorm.
 * User: elvis
 * Date: 08/08/2016
 * Time: 15:46.
 */

namespace AppBundle\Entity\Ndr\Traits;

use AppBundle\Entity\Ndr\OneOff;
use AppBundle\Entity\Ndr\StateBenefit;

trait IncomeBenefitTrait
{
    /**
     * @var StateBenefit[]
     *
     * @JMS\Groups({"state-benefits"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Ndr\StateBenefit", mappedBy="odr")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $stateBenefits;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"pension"})
     * @ORM\Column(name="receive_state_pension", type="text", nullable=true)
     */
    private $receiveStatePension;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"pension"})
     * @ORM\Column(name="receive_other_income", type="text", nullable=true)
     */
    private $receiveOtherIncome;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"pension"})
     * @ORM\Column(name="receive_other_income_details", type="text", nullable=true)
     */
    private $receiveOtherIncomeDetails;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"damages"})
     * @ORM\Column(name="expect_compensation_damages", type="text", nullable=true)
     */
    private $expectCompensationDamages;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"damages"})
     * @ORM\Column(name="expect_compensation_damages_details", type="text", nullable=true)
     */
    private $expectCompensationDamagesDetails;

    /**
     * @var OneOff[]
     *
     * @JMS\Groups({"one-off"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Ndr\OneOff", mappedBy="odr")
     * @ORM\OrderBy({"id" = "ASC"})
     */
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
