<?php

namespace AppBundle\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="prof_service_fee")
 * @ORM\Entity
 */
class ProfServiceFee
{
    const TYPE_ASSESSED_FEE = 'assessed';
    const TYPE_FIXED_FEE = 'fixed';

    const TYPE_PREVIOUS_FEE = 'previous';
    const TYPE_CURRENT_FEE = 'current';
    const TYPE_ESTIMATED_FEE = 'estimated';

    /**
     * Hold service type
     *
     * If the order or any key is added, update the ReportControllerTest, hardcoded on position and number
     *  in order to keep it simple
     *
     * @var array
     */
    public static $serviceTypeIds = [
        // id => hasMoreDetails
        'annual-report' => false,
        'annual-management-interim' => false,
        'annual-management-final' => false,
        'appointment' => false,
        'conveyancing' => false,
        'litigation' => true,
        'specialist-advice' => true,
        'statutory-wills' => false,
        'tax-returns' => false,
        'trust-applications' => false,
        'other-costs' => false
    ];


    /**
     * Static list of fee type ids
     * previous, current or estimated
     *
     * @var array
     */
    public static $feeTypeIds = [
        // id => hasMoreDetails
        'previous' => false,
        'estimated' => false,
        'current' => false
    ];

    /**
     * @var int
     * @JMS\Type("integer")
     *
     * @JMS\Groups({"prof-service-fees"})
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report\Report", inversedBy="prof-service-fees")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $report;

    /**
     * @JMS\Type("string")
     * @var string fixed|assessed
     * @JMS\Groups({"prof-service-fees"})
     *
     * @ORM\Column(name="assessed_or_fixed", type="string", nullable=true)
     */
    private $assessedOrFixed;

    /**
     * @JMS\Type("string")
     * @var string a value in self:$feeTypeIds
     *
     * @JMS\Groups({"prof-service-fees"})
     *
     * @ORM\Column(name="fee_type_id", type="string", nullable=false)
     */
    private $feeTypeId;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"prof-service-fees"})
     *
     * @ORM\Column(name="other_fee_details", type="string", nullable=true)
     */
    private $otherFeeDetails;

    /**
     * @JMS\Type("string")
     * @var string a value in self:$serviceTypeIds
     *
     * @JMS\Groups({"prof-service-fees"})
     *
     * @ORM\Column(name="service_type_id", type="string", nullable=false)
     */
    private $serviceTypeId;

    /**
     * @var float
     *
     * @JMS\Type("string")
     * @JMS\Groups({"prof-service-fees"})
     *
     * @ORM\Column(name="amount_charged", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $amountCharged;

    /**
     * @JMS\Type("string")
     * @var string yes|no
     *
     * @JMS\Groups({"prof-service-fees"})
     *
     * @ORM\Column(name="payment_received", type="string", nullable=true)
     */
    private $paymentReceived;

    /**
     * @var decimal
     *
     * @JMS\Type("string")
     * @JMS\Groups({"prof-service-fees"})
     *
     * @ORM\Column(name="amount_received", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $amountReceived;

    /**
     * @var \DateTime
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"prof-service-fees"})
     *
     * @ORM\Column(name="payment_received_date", type="datetime", nullable=true)
     */
    private $paymentReceivedDate;

    /**
     * @param Report $report
     * @param string $serviceTypeId
     * @param float  $amount
     */
    public function __construct(Report $report)
    {
        $this->setReport($report);
        //$report->addProfServiceFee($this);

        //$this->setFeeTypeId($feeTypeId);
        //$this->setServiceTypeId($serviceTypeId);
        //$this->setAmount($amount);
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
    public function getAssessedOrFixed()
    {
        return $this->assessedOrFixed;
    }

    /**
     * @param string $assessedOrFixed
     */
    public function setAssessedOrFixed($assessedOrFixed)
    {
        $this->assessedOrFixed = $assessedOrFixed;
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
     * @return mixed
     */
    public function getOtherFeeDetails()
    {
        return $this->otherFeeDetails;
    }

    /**
     * @param mixed $otherFeeDetails
     */
    public function setOtherFeeDetails($otherFeeDetails)
    {
        $this->otherFeeDetails = $otherFeeDetails;
    }

    /**
     * @return decimal
     */
    public function getAmountCharged()
    {
        return $this->amountCharged;
    }

    /**
     * @param decimal $amountCharged
     */
    public function setAmountCharged($amountCharged)
    {
        $this->amountCharged = $amountCharged;
    }

    /**
     * @return decimal
     */
    public function getAmountReceived()
    {
        return $this->amountReceived;
    }

    /**
     * @param decimal $amountReceived
     */
    public function setAmountReceived($amountReceived)
    {
        $this->amountReceived = $amountReceived;
    }

    /**
     * @return mixed
     */
    public function getPaymentReceivedDate()
    {
        return $this->paymentReceivedDate;
    }

    /**
     * @param mixed $paymentReceivedDate
     */
    public function setPaymentReceivedDate($paymentReceivedDate)
    {
        $this->paymentReceivedDate = $paymentReceivedDate;
    }

    /**
     * @return string
     */
    public function getPaymentReceived()
    {
        return $this->paymentReceived;
    }

    /**
     * @param string $paymentReceived
     */
    public function setPaymentReceived($paymentReceived)
    {
        $this->paymentReceived = $paymentReceived;
    }

    /**
     * @return string
     */
    public function getServiceTypeId()
    {
        return $this->serviceTypeId;
    }

    /**
     * @param string $serviceTypeId
     */
    public function setServiceTypeId($serviceTypeId)
    {
        $this->serviceTypeId = $serviceTypeId;
    }

    /**
     * Is a current Fee?
     *
     * @return bool
     */
    public function isCurrentFee()
    {
        return $this->getFeeTypeId() == self::TYPE_CURRENT_FEE;
    }

    /**
     * Is a previous Fee?
     *
     * @return bool
     */
    public function isPreviousFee()
    {
        return $this->getFeeTypeId() == self::TYPE_PREVIOUS_FEE;
    }

    /**
     * Is a estimated Fee?
     *
     * @return bool
     */
    public function isEstimatedFee()
    {
        return $this->getFeeTypeId() == self::TYPE_ESTIMATED_FEE;
    }

    /**
     * Is a fixed Fee?
     *
     * @return bool
     */
    public function isFixedFee()
    {
        return $this->getAssessedOrFixed() == self::TYPE_FIXED_FEE;
    }

    /**
     * Is a assessed Fee?
     *
     * @return bool
     */
    public function isAssessedFee()
    {
        return $this->getAssessedOrFixed() == self::TYPE_ASSESSED_FEE;
    }
}
