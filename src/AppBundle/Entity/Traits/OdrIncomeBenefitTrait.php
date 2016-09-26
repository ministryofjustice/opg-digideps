<?php
/**
 * Created by PhpStorm.
 * User: elvis
 * Date: 08/08/2016
 * Time: 15:46.
 */

namespace AppBundle\Entity\Traits;

use AppBundle\Entity\Odr\IncomeBenefitOneOff;
use AppBundle\Entity\Odr\IncomeBenefitStateBenefit;

trait OdrIncomeBenefitTrait
{
    /**
     * @var IncomeBenefit[]
     *
     * @JMS\Groups({"odr-income-state-benefits"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Odr\IncomeBenefitStateBenefit", mappedBy="odr")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $stateBenefits;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-income-pension"})
     * @ORM\Column(name="receive_state_pension", type="text", nullable=true)
     */
    private $receiveStatePension;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-income-pension"})
     * @ORM\Column(name="receive_other_income", type="text", nullable=true)
     */
    private $receiveOtherIncome;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-income-pension"})
     * @ORM\Column(name="receive_other_income_details", type="text", nullable=true)
     */
    private $receiveOtherIncomeDetails;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-income-damages"})
     * @ORM\Column(name="expect_compensation_damages", type="text", nullable=true)
     */
    private $expectCompensationDamages;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-income-damages"})
     * @ORM\Column(name="expect_compensation_damages_details", type="text", nullable=true)
     */
    private $expectCompensationDamagesDetails;

    /**
     * @var IncomeBenefit[]
     *
     * @JMS\Groups({"odr-income-one-off"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Odr\IncomeBenefitOneOff", mappedBy="odr")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $oneOff;

    /**
     * @return IncomeBenefit[]
     */
    public function getStateBenefits()
    {
        return $this->stateBenefits;
    }

    /**
     * @param IncomeBenefit[] $stateBenefits
     *
     * @return OdrIncomeBenefitTrait
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
     * @return OdrIncomeBenefitTrait
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
     * @return OdrIncomeBenefitTrait
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
     * @return OdrIncomeBenefitTrait
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
     * @return OdrIncomeBenefitTrait
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
     * @return OdrIncomeBenefitTrait
     */
    public function setReceiveOtherIncome($receiveOtherIncome)
    {
        $this->receiveOtherIncome = $receiveOtherIncome;

        return $this;
    }

    /**
     * @return IncomeBenefit[]
     */
    public function getOneOff()
    {
        return $this->oneOff;
    }

    /**
     * @param IncomeBenefit[] $oneOff
     *
     * @return OdrIncomeBenefitTrait
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
     * @return IncomeBenefitOneOff
     */
    public function getOneOffByTypeId($typeId)
    {
        return $this->getOneOff()->filter(function (IncomeBenefitOneOff $incomeOneOff) use ($typeId) {
            return $incomeOneOff->getTypeId() == $typeId;
        })->first();
    }

    /**
     * @param string $typeId
     *
     * @return IncomeBenefitStateBenefit
     */
    public function getStateBenefitByTypeId($typeId)
    {
        return $this->getStateBenefits()->filter(function (IncomeBenefitStateBenefit $sb) use ($typeId) {
            return $sb->getTypeId() == $typeId;
        })->first();
    }
}
