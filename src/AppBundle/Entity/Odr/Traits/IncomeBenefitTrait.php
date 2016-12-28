<?php

namespace AppBundle\Entity\Odr\Traits;

use AppBundle\Entity\Odr\IncomeBenefit;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait IncomeBenefitTrait
{
    /**
     * @JMS\Type("array<AppBundle\Entity\Odr\IncomeBenefit>")
     * @JMS\Groups({"odr-state-benefits"})
     *
     * @var IncomeBenefit[]
     */
    private $stateBenefits;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-receive-state-pension"})
     * @Assert\NotBlank(message="odr.incomeBenefit.receiveStatePension.notBlank", groups={"receive-state-pension"})
     */
    private $receiveStatePension;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-receive-other-income"})
     * @Assert\NotBlank(message="odr.incomeBenefit.receiveOtherIncome.notBlank", groups={"receive-other-income"})
     */
    private $receiveOtherIncome;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-receive-other-income"})
     * @Assert\NotBlank(message="odr.incomeBenefit.receiveOtherIncomeDetails.notBlank", groups={"receive-other-income-details"})
     */
    private $receiveOtherIncomeDetails;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-income-damages"})
     * @Assert\NotBlank(message="odr.incomeBenefit.expectCompensationDamages.notBlank", groups={"expect-compensation-damage"})
     */
    private $expectCompensationDamages;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-income-damages"})
     * @Assert\NotBlank(message="odr.incomeBenefit.expectCompensationDamagesDetails.notBlank", groups={"expect-compensation-damage-details"})
     */
    private $expectCompensationDamagesDetails;

    /**
     * @JMS\Type("array<AppBundle\Entity\Odr\IncomeBenefit>")
     * @JMS\Groups({"odr-one-off"})
     *
     * @var IncomeBenefit[]
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
     * @return IncomeBenefit
     */
    public function getStateBenefitOther()
    {
        foreach($this->stateBenefits as $st) {
            if ($st->getTypeId() == 'other_benefits') {
                return $st;
            }
        }
    }

    /**
     * @param IncomeBenefit $stateBenefits
     *
     * @return Odr
     */
    public function setStateBenefits($stateBenefits)
    {
        $this->stateBenefits = $stateBenefits;

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
     * @return Odr
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
     * @return Odr
     */
    public function setReceiveOtherIncome($receiveOtherIncome)
    {
        $this->receiveOtherIncome = $receiveOtherIncome;

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
    public function setOneOff($oneOff)
    {
        $this->oneOff = $oneOff;

        return $this;
    }

    /**
     * @param IncomeBenefit[] $elements
     *
     * @return int
     */
    public function recordsPresent($elements)
    {
        if (empty($elements) || !is_array($elements)) {
            return 0;
        }

        return array_filter($elements, function ($st) {
            return $st instanceof IncomeBenefit && $st->isPresent();
        });
    }

    /**
     * Used from OdrStatusService.
     *
     * @return string not-started/incomplete/done
     */
    public function incomeBenefitsStatus()
    {
        $stCount = count($this->recordsPresent($this->getStateBenefits()));
        $ooCount = count($this->recordsPresent($this->getOneOff()));
        $statePens = $this->getReceiveStatePension();
        $otherInc = $this->getReceiveOtherIncome();
        $compensDamag = $this->getExpectCompensationDamages();

        if ($stCount === 0
            && $statePens == null && $otherInc == null && $compensDamag == null
            && $ooCount === 0
        ) {
            return 'not-started';
        }

        if ($statePens != null && $otherInc != null && $compensDamag != null) {
            return 'done';
        }

        return 'incomplete';
    }

}
