<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Report\Traits\HasReportTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class ProfServiceFee
{
    use HasReportTrait;

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
     * @JMS\Groups({"prof_service_fee"})
     */
    private $assessedOrFixed;

    /**
     * @var string a value in self:$feeTypeIds
     *
     * @JMS\Groups({"prof_service_fee"})
     */
    private $feeTypeId;

    /**
     * @var string a value in self:$serviceTypeIds
     *
     * @JMS\Groups({"prof_service_fee"})
     */
    private $serviceTypeId;

    /**
     * @var decimal
     *
     * @JMS\Type("string")
     * @JMS\Groups({"prof_service_fee"})
     * @Assert\Type(type="numeric", message="fee.amount.notNumeric", groups={"prof_service_fee"})
     * @Assert\Range(min=0, max=100000000, minMessage = "fee.amount.minMessage", maxMessage = "fee.amount.maxMessage", groups={"fees"})
     */
    private $amountCharged;


    /**
     * @var string yes|no
     *
     * @JMS\Groups({"prof_service_fee"})
     */
    private $paymentReceived;

    /**
     * @var decimal
     *
     * @JMS\Type("string")
     * @JMS\Groups({"prof_service_fee"})
     * @Assert\Type(type="numeric", message="fee.amount.notNumeric", groups={"prof_service_fee"})
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
