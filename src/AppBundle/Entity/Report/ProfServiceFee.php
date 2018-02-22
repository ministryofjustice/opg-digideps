<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Report\Traits\HasReportTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class ProfServiceFee
{
    use HasReportTrait;

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
    private $amount;

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
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param decimal $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }
}
