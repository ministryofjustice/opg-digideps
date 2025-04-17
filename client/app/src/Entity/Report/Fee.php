<?php

namespace App\Entity\Report;

use App\Entity\Report\Traits\HasReportTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Fee
{
    use HasReportTrait;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"fee"})
     */
    private $feeTypeId;
    /**
     * @var decimal
     *
     * @JMS\Type("string")
     * @JMS\Groups({"fee"})
     * @Assert\Type(type="numeric", message="fee.amount.notNumeric", groups={"fees"})
     * @Assert\Range(min=0, max=100000000000, notInRangeMessage = "fee.amount.notInRangeMessage", groups={"fees"})
     */
    private $amount;

    /**
     * @var string
     * @JMS\Groups({"fee"})
     * @JMS\Type("boolean")
     */
    private $hasMoreDetails;

    /**
     * @var string
     * @JMS\Groups({"fee"})
     * @JMS\Type("string")
     *
     * @Assert\NotBlank(message="fee.moreDetails.notEmpty", groups={"fees-more-details"})
     */
    private $moreDetails;

    /**
     * @var int
     * @JMS\Groups({"fee"})
     * @JMS\Type("int")
     */
    private $id;

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
}
