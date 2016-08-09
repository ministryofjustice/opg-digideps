<?php
/**
 * Created by PhpStorm.
 * User: elvis
 * Date: 08/08/2016
 * Time: 15:46
 */

namespace AppBundle\Entity\Traits;


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
     * @return OdrIncomeBenefitTrait
     */
    public function setExpectCompensationDamages($expectCompensationDamages)
    {
        $this->expectCompensationDamages = $expectCompensationDamages;
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
     * @return IncomeOneOff
     */
//    public function getIncomeOneOffByTypeId($typeId)
//    {
//        return $this->getIncomeOneOff()->filter(function (IncomeOneOff $incomeOneOff) use ($typeId) {
//            return $incomeOneOff->getTypeId() == $typeId;
//        })->first();
//    }


}