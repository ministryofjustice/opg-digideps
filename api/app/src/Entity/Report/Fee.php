<?php

namespace App\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="fee")
 *
 * @ORM\Entity
 */
class Fee
{
    /**
     * Hold fee type
     * 1st value = id, 2nd value = hasMoreInformation.
     * If the order or any key is added, update the ReportControllerTest, hardcoded on position and number
     *  in order to keep it simple.
     *
     * @var array
     */
    public static $feeTypeIds = [
        'work-up-to-and-including-cot-made' => false,
        'annual-management-fee' => false,
        'annual-property-management-fee' => false,
        'preparing-and-lodging-annual-report' => false,
        'completition-of-tax-return' => false,
        'travel-costs' => true,
        'specialist-service' => true,
    ];

    /**
     * @var int
     *
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="fee_id_seq", allocationSize=1, initialValue=1)
     */
    #[JMS\Groups(['fee'])]
    private $id;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Report\Report", inversedBy="fees")
     *
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $report;

    /**
     * @var string a value in self:$feeTypeIds
     *
     *
     * @ORM\Column(name="fee_type_id", type="string", nullable=false)
     */
    #[JMS\Groups(['fee'])]
    private $feeTypeId;

    /**
     * @var string|null
     *
     *
     *
     * @ORM\Column(name="amount", type="decimal", precision=14, scale=2, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['fee'])]
    private $amount;

    /**
     * @var string
     *
     *
     * @ORM\Column(name="more_details", type="text", nullable=true)
     */
    #[JMS\Groups(['fee'])]
    private $moreDetails;

    /**
     * @param string      $feeTypeId
     * @param string|null $amount
     */
    public function __construct(Report $report, $feeTypeId, $amount = null)
    {
        $this->report = $report;
        $report->addFee($this);

        $this->feeTypeId = $feeTypeId;
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
     * @return string|null
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param string $amount
     *
     * @return Fee
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

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
    }

    /**
     *
     *
     *
     *
     * @return bool
     */
    #[JMS\Type('boolean')]
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('has_more_details')]
    #[JMS\Groups(['fee'])]
    public function getHasMoreDetails()
    {
        return self::$feeTypeIds[$this->getFeeTypeId()];
    }

    public function setAmountAndDetails($amount, $details)
    {
        $this->setAmount($amount);

        // reset details if amount is not given, or if more details are not expected
        if (empty($amount) || !$this->getHasMoreDetails()) {
            $details = null;
        }

        $this->setMoreDetails($details);
    }
}
