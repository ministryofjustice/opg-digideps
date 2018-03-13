<?php

namespace AppBundle\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity()
 * @ORM\Table(name="prof_service_fee")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="fee_type_id", type="string")
 * @ORM\DiscriminatorMap({
 *      "current"  = "AppBundle\Entity\Report\ProfServiceFeeCurrent",
 * })
 */
abstract class ProfServiceFee
{
    const TYPE_ASSESSED_FEE = 'assessed';
    const TYPE_FIXED_FEE = 'fixed';

    const TYPE_PREVIOUS_FEE = 'previous';
    const TYPE_CURRENT_FEE = 'current';
    const TYPE_ESTIMATED_FEE = 'estimated';

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
     * Discriminator field
     *
     * @var string
     * @JMS\Exclude
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
     */
    public function __construct(Report $report)
    {
        $this->setReport($report);
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
     * @JMS\VirtualProperty
     * @JMS\Type("string")
     * @JMS\Groups({"prof-service-fees"})
     *
     * @return string
     */
    abstract public function getFeeTypeId();

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
     * @return string
     */
    public function getAmountCharged()
    {
        return $this->amountCharged;
    }

    /**
     * @param string $amountCharged
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
