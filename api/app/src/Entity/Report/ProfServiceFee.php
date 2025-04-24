<?php

namespace App\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity()
 *
 * @ORM\Table(name="prof_service_fee")
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 *
 * @ORM\DiscriminatorColumn(name="fee_type_id", type="string")
 *
 * @ORM\DiscriminatorMap({
 *      "current"  = "App\Entity\Report\ProfServiceFeeCurrent",
 * })
 */
abstract class ProfServiceFee
{
    public const TYPE_ASSESSED_FEE = 'assessed';
    public const TYPE_FIXED_FEE = 'fixed';

    public const TYPE_PREVIOUS_FEE = 'previous';
    public const TYPE_CURRENT_FEE = 'current';
    public const TYPE_ESTIMATED_FEE = 'estimated';

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
     * @ORM\SequenceGenerator(sequenceName="fee_id_seq", allocationSize=1, initialValue=1)
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['prof-service-fees'])]
    private $id;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Report\Report", inversedBy="profServiceFees")
     *
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $report;

    /**
     *
     * @var string fixed|assessed
     *
     *
     * @ORM\Column(name="assessed_or_fixed", type="string", nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-service-fees'])]
    private $assessedOrFixed;

    /**
     * Discriminator field.
     *
     * @var string
     */
    #[JMS\Exclude]
    private $feeTypeId;

    /**
     *
     *
     * @ORM\Column(name="other_fee_details", type="string", nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-service-fees'])]
    private $otherFeeDetails;

    /**
     *
     * @var string a value in self:$serviceTypeIds
     *
     *
     * @ORM\Column(name="service_type_id", type="string", nullable=false)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-service-fees'])]
    private $serviceTypeId;

    /**
     * @var float
     *
     *
     *
     * @ORM\Column(name="amount_charged", type="decimal", precision=14, scale=2, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-service-fees'])]
    private $amountCharged;

    /**
     *
     * @var string yes|no
     *
     *
     * @ORM\Column(name="payment_received", type="string", nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-service-fees'])]
    private $paymentReceived;

    /**
     * @var decimal
     *
     *
     *
     * @ORM\Column(name="amount_received", type="decimal", precision=14, scale=2, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-service-fees'])]
    private $amountReceived;

    /**
     * @var \DateTime
     *
     *
     *
     * @ORM\Column(name="payment_received_date", type="datetime", nullable=true)
     */
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['prof-service-fees'])]
    private $paymentReceivedDate;

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
     *
     *
     *
     * @return string
     */
    #[JMS\VirtualProperty]
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-service-fees'])]
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
        return self::TYPE_FIXED_FEE == $this->getAssessedOrFixed();
    }

    /**
     * Is a assessed Fee?
     *
     * @return bool
     */
    public function isAssessedFee()
    {
        return self::TYPE_ASSESSED_FEE == $this->getAssessedOrFixed();
    }
}
