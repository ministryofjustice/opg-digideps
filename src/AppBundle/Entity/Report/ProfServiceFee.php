<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Report\Traits\HasReportTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class ProfServiceFee
{
    use HasReportTrait;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

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
     * @var string fixed|assessed
     *
     * @JMS\Groups({"prof-service-fees"})
     */
    private $assessedOrFixed;

    /**
     * @var string a value in self:$feeTypeIds
     *
     * @JMS\Groups({"prof-service-fees"})
     */
    private $feeTypeId;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"prof-service-fees"})
     *
     * @Assert\NotBlank(message="fee.otherFeeDetails.notBlank", groups={"other-prof-service-fees"})
     */
    private $otherFeeDetails;

    /**
     * @var string a value in self:$serviceTypeIds
     *
     * @JMS\Groups({"prof-service-fees"})
     */
    private $serviceTypeId;

    /**
     * @var decimal
     *
     * @JMS\Type("string")
     * @JMS\Groups({"prof-service-fees"})
     * @Assert\Type(type="numeric", message="fee.amount.notNumeric", groups={"prof-service-fees"})
     * @Assert\Range(min=0, max=100000000, minMessage = "fee.amount.minMessage", maxMessage = "fee.amount.maxMessage", groups={"fees"})
     */
    private $amountCharged;


    /**
     * @var string yes|no
     *
     * @JMS\Groups({"prof-service-fees"})
     */
    private $paymentReceived;

    /**
     * @var decimal
     *
     * @JMS\Type("string")
     * @JMS\Groups({"prof-service-fees"})
     * @Assert\Type(type="numeric", message="fee.amount.notNumeric", groups={"prof-service-fees"})
     * @Assert\Range(min=0, max=100000000, minMessage = "fee.amount.minMessage", maxMessage = "fee.amount.maxMessage", groups={"fees"})
     */
    private $amountReceived;

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @var \DateTime
     */
    private $paymentReceivedDate;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return mixed
     */
    public function getFeeTypeId()
    {
        return $this->feeTypeId;
    }

    /**
     * @param mixed $feeTypeId
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
}
