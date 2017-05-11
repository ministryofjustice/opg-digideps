<?php

namespace AppBundle\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="fee")
 * @ORM\Entity
 */
class Fee
{
    /**
     * Hold fee type
     * 1st value = id, 2nd value = hasMoreInformation.
     *
     * @var array
     */
    public static $feeTypeIds = [
        ['work-up-to-and-including-cot-made', false],
        ['annual-management-fee', false],
        ['annual-property-management-fee', false],
        ['preparing-and-lodging-annual-report', false],
        ['completition-of-tax-return', false],
        ['travel-costs', true],
        ['specialist-service', false],
    ];

    /**
     * @var int
     *
     * @JMS\Groups({"fee"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="fee_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report\Report", inversedBy="fees")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $report;

    /**
     * @var string a value in self:$feeTypeIds
     *
     * @JMS\Groups({"fee"})
     *
     * @ORM\Column(name="fee_type_id", type="string", nullable=false)
     */
    private $feeTypeId;

    /**
     * @var float
     *
     * @JMS\Type("string")
     * @JMS\Groups({"fee"})
     *
     * @ORM\Column(name="amount", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $amount;

    /**
     * @var bool
     *
     * @JMS\Groups({"fee"})
     * @JMS\Type("boolean")
     *
     * @ORM\Column(name="has_more_details", type="boolean", nullable=false)
     */
    private $hasMoreDetails;

    /**
     * @var string
     *
     * @JMS\Groups({"fee"})
     *
     * @ORM\Column(name="more_details", type="text", nullable=true)
     */
    private $moreDetails;

    /**
     * @param Report $report
     * @param string $feeTypeId
     * @param bool   $hasMoreDetails
     * @param float  $amount
     */
    public function __construct(Report $report, $feeTypeId, $hasMoreDetails, $amount)
    {
        $this->report = $report;
        $report->addFee($this);

        $this->feeTypeId = $feeTypeId;
        $this->hasMoreDetails = $hasMoreDetails;
        $this->amount = $amount;
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
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param Report $report
     */
    public function setReport($report)
    {
        $this->report = $report;
    }

    /**
     * @return string
     */
    public function getFeeTypeId()
    {
        return $this->feeTypeId;
    }

    /**
     * @param string $feeTypeId
     */
    public function setFeeTypeId($feeTypeId)
    {
        $this->feeTypeId = $feeTypeId;
    }


    /**
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param string $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
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

    /**
     * @return bool
     */
    public function getHasMoreDetails()
    {
        return $this->hasMoreDetails;
    }

    /**
     * @param bool $hasMoreDetails
     */
    public function setHasMoreDetails($hasMoreDetails)
    {
        $this->hasMoreDetails = $hasMoreDetails;
    }
}
