<?php

namespace AppBundle\Entity\Odr;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class IncomeBenefit
{
//    public static $stateBenefitsKeys = [
//        'contributionsBasedAllowance' => false,
//        'incomeSupportPensionGuaranteeCredit' => false,
//        'incomerelatedEmploymentSupportAllowance' => false,
//        'incomebasedJobSeekerAllowance' => false,
//        'housingBenefit' => false,
//        'universalCredit' => false,
//        'severeDisablementAllowance' => false,
//        'disabilityLivingAllowance' => false,
//        'attendanceAllowance' => false,
//        'prsonalIndependencePayment' => false,
//        'workingChildTaxCredits' => false,
//        'otherBenefits' => true,
//    ];

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"odr-income-benefit"})
     */
    private $typeId;

    /**
     * @var boolean
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"odr-income-benefit"})
     */
    private $present;

    /**
     * @var string
     * @JMS\Groups({"odr-income-benefit"})
     * @JMS\Type("boolean")
     */
    private $hasMoreDetails;

    /**
     * @var string
     * @JMS\Groups({"odr-income-benefit"})
     * @JMS\Type("string")
     *
     * @Assert\NotBlank(message="odr.incomeBenefit.moreDetails.notEmpty", groups={"income-benefit-more-details"})
     */
    private $moreDetails;

    /**
     * IncomeBenefit constructor.
     * @param $typeId
     * @param bool $present
     * @param string $hasMoreDetails
     * @param string $moreDetails
     */
    public function __construct($typeId, $present, $hasMoreDetails = false, $moreDetails = null)
    {
        $this->typeId = $typeId;
        $this->present = $present;
        $this->hasMoreDetails = $hasMoreDetails;
        $this->moreDetails = $moreDetails;
    }

    /**
     * @return mixed
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * @param mixed $typeId
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;
    }

    /**
     * @return boolean
     */
    public function isPresent()
    {
        return $this->present;
    }

    /**
     * @param boolean $present
     */
    public function setPresent($present)
    {
        $this->present = $present;
    }

    /**
     * @return string
     */
    public function getHasMoreDetails()
    {
        return $this->hasMoreDetails;
    }

    /**
     * @param string $hasMoreDetails
     */
    public function setHasMoreDetails($hasMoreDetails)
    {
        $this->hasMoreDetails = $hasMoreDetails;
    }

    /**
     * @return string
     */
    public function getMoreDetails()
    {
        return $this->moreDetails;
    }

    /**
     * @param string $moreDetails
     */
    public function setMoreDetails($moreDetails)
    {
        $this->moreDetails = $moreDetails;
    }


}
