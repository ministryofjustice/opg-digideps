<?php

namespace App\Entity\Ndr;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 *
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
     *
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\SequenceGenerator(sequenceName="odr_state_benefits_id_seq", allocationSize=1, initialValue=1)
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['state-benefits'])]
    private $id;

    /**
     * @var Ndr
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Ndr\Ndr", inversedBy="stateBenefits")
     *
     * @ORM\JoinColumn(name="odr_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $ndr;

    /**
     * @var string key from self::$stateBenefitsKeys
     *
     *
     * @ORM\Column(name="type_id", type="string", nullable=false)
     */
    #[JMS\Groups(['state-benefits'])]
    private $typeId;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="present", type="boolean", nullable=true)
     */
    #[JMS\Type('boolean')]
    #[JMS\Groups(['state-benefits'])]
    private $present;

    /**
     * @var string
     *
     *
     * @ORM\Column(name="has_more_details", type="string", nullable=false)
     */
    #[JMS\Groups(['state-benefits'])]
    private $hasMoreDetails;

    /**
     * @var string
     *
     *
     * @ORM\Column(name="more_details", type="string", nullable=true)
     */
    #[JMS\Groups(['state-benefits'])]
    private $moreDetails;

    /**
     * @param string $typeId
     */
    public function __construct(Ndr $ndr, $typeId, $hasMoreDetails)
    {
        $this->ndr = $ndr;
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
     * @return Ndr
     */
    public function getNdr()
    {
        return $this->ndr;
    }

    /**
     * @param Ndr $ndr
     */
    public function setNdr($ndr)
    {
        $this->ndr = $ndr;
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
