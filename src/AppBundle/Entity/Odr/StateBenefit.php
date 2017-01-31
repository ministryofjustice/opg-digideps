<?php

namespace AppBundle\Entity\Odr;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="odr_income_state_benefit")
 */
class StateBenefit
{
    public static $stateBenefitsKeys = [
        'contributions_based_allowance' => false,
        'income_support_pension_guarantee_credit' => false,
        'income_related_employment_support_allowance' => false,
        'income_based_job_seeker_allowance' => false,
        'housing_benefit' => false,
        'universal_credit' => false,
        'severe_disablement_allowance' => false,
        'disability_living_allowance' => false,
        'attendance_allowance' => false,
        'personal_independence_payment' => false,
        'working_child_tax_credits' => false,
        'other_benefits' => true,
    ];

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\Groups({"state-benefits"})
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="odr_state_benefits_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Odr
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Odr\Odr")
     * @ORM\JoinColumn(name="odr_id", referencedColumnName="id")
     */
    private $odr;


    /**
     * @var string key from self::$stateBenefitsKeys
     *
     * @JMS\Groups({"state-benefits"})
     * @ORM\Column(name="type_id", type="string", nullable=false)
     */
    private $typeId;

    /**
     * @var string
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"state-benefits"})
     * @ORM\Column(name="present", type="boolean", nullable=true)
     */
    private $present;

    /**
     * @var string
     *
     * @JMS\Groups({"state-benefits"})
     * @ORM\Column(name="has_more_details", type="string", nullable=false)
     */
    private $hasMoreDetails;

    /**
     * @var string
     * @JMS\Groups({"state-benefits"})
     * @ORM\Column(name="more_details", type="string", nullable=true)
     */
    private $moreDetails;

    /**
     * @param Odr    $odr
     * @param string $typeId
     * @param float  $amount
     */
    public function __construct(Odr $odr, $typeId, $hasMoreDetails)
    {
        $this->odr = $odr;
        $this->typeId = $typeId;
        $this->present = null;
        $this->hasMoreDetails = $hasMoreDetails;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return Odr
     */
    public function getOdr()
    {
        return $this->odr;
    }

    /**
     * @param Odr $odr
     */
    public function setOdr($odr)
    {
        $this->odr = $odr;
    }

    /**
     * @return string
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * @param string $typeId
     *
     * @return IncomeOneOff
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;

        return $this;
    }

    /**
     * @return string
     */
    public function getPresent()
    {
        return $this->present;
    }

    /**
     * @param string $present
     *
     * @return IncomeBenefit
     */
    public function setPresent($present)
    {
        $this->present = $present;

        return $this;
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

        return $this;
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

        return $this;
    }
}
