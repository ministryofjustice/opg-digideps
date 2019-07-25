<?php

namespace AppBundle\Entity\Ndr\Traits;

use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Ndr\StateBenefit;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportIncomeBenefitTrait
{
    /**
     * @JMS\Type("array<AppBundle\Entity\Ndr\StateBenefit>")
     * @JMS\Groups({"ndr-state-benefits"})
     *
     * @var StateBenefit[]
     */
    private $stateBenefits = [];

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"ndr-receive-state-pension"})
     * @Assert\NotBlank(message="ndr.incomeBenefit.receiveStatePension.notBlank", groups={"receive-state-pension"})
     */
    private $receiveStatePension;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"ndr-receive-other-income"})
     * @Assert\NotBlank(message="ndr.incomeBenefit.receiveOtherIncome.notBlank", groups={"receive-other-income"})
     */
    private $receiveOtherIncome;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"ndr-receive-other-income"})
     * @Assert\NotBlank(message="ndr.incomeBenefit.receiveOtherIncomeDetails.notBlank", groups={"receive-other-income-details"})
     */
    private $receiveOtherIncomeDetails;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"ndr-income-damages"})
     * @Assert\NotBlank(message="ndr.incomeBenefit.expectCompensationDamages.notBlank", groups={"expect-compensation-damage"})
     */
    private $expectCompensationDamages;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"ndr-income-damages"})
     * @Assert\NotBlank(message="ndr.incomeBenefit.expectCompensationDamagesDetails.notBlank", groups={"expect-compensation-damage-details"})
     */
    private $expectCompensationDamagesDetails;

    /**
     * @JMS\Type("array<AppBundle\Entity\Ndr\OneOff>")
     * @JMS\Groups({"ndr-one-off"})
     *
     * @var OneOff[]
     */
    private $oneOff = [];

    /**
     * @return StateBenefit[]
     */
    public function getStateBenefits()
    {
        return $this->stateBenefits;
    }

    /**
     * @return StateBenefit[]
     */
    public function getStateBenefitsPresent()
    {
        return array_filter($this->stateBenefits ?: [], function ($st) {
            return method_exists($st, 'isPresent') && $st->isPresent();
        });
    }

    /**
     * @return StateBenefit
     */
    public function getStateBenefitOther()
    {
        foreach ($this->stateBenefits as $st) {
            if ($st->getTypeId() == 'other_benefits') {
                return $st;
            }
        }
    }

    /**
     * @param StateBenefit[] $stateBenefits
     *
     * @return Ndr
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
     * @return Ndr
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
     * @return Ndr
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
     * @return Ndr
     */
    public function setExpectCompensationDamagesDetails($expectCompensationDamagesDetails)
    {
        $this->expectCompensationDamagesDetails = $expectCompensationDamagesDetails;

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
     * @return StateBenefit[]
     */
    public function getOneOffPresent()
    {
        return array_filter($this->oneOff ?: [], function ($st) {
            return method_exists($st, 'isPresent') && $st->isPresent();
        });
    }

    /**
     * @param OneOff[] $oneOff
     *
     * @return NdrIncomeBenefitTrait
     */
    public function setOneOff($oneOff)
    {
        $this->oneOff = $oneOff;

        return $this;
    }

    /**
     * Used from NdrStatusService.
     *
     * @return string not-started/incomplete/done
     */
    public function incomeBenefitsStatus()
    {
        $stCount = count($this->getStateBenefitsPresent());
        $ooCount = count($this->getOneOffPresent());
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
