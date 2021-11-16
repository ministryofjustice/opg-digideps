<?php

namespace App\Entity\Ndr;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Debt
{
    /**
     * Debt constructor.
     *
     * @param $debtTypeId
     * @param decimal $amount
     * @param string  $hasMoreDetails
     * @param string  $moreDetails
     */
    public function __construct(
        /**
         * @JMS\Type("string")
         * @JMS\Groups({"debt"})
         */
        private $debtTypeId,
        /**
         *
         * @JMS\Type("string")
         * @JMS\Groups({"debt"})
         * @Assert\Type(type="numeric", message="ndr.debt.amount.notNumeric", groups={"debts"})
         * @Assert\Range(min=0, max=100000000000, notInRangeMessage = "ndr.debt.amount.notInRangeMessage", groups={"debts"})
         */
        private $amount,
        /**
         * @JMS\Groups({"debt"})
         * @JMS\Type("boolean")
         */
        private $hasMoreDetails,
        /**
         * @JMS\Groups({"debt"})
         * @JMS\Type("string")
         * @Assert\NotBlank(message="ndr.debt.moreDetails.notEmpty", groups={"debts-more-details"})
         */
        private $moreDetails
    )
    {
    }

    /**
     * @return array
     */
    public static function getDebtTypeIds()
    {
        return self::$debtTypeIds;
    }

    /**
     * @param array $debtTypeIds
     */
    public static function setDebtTypeIds($debtTypeIds)
    {
        self::$debtTypeIds = $debtTypeIds;
    }

    /**
     * @return mixed
     */
    public function getDebtTypeId()
    {
        return $this->debtTypeId;
    }

    /**
     * @param mixed $debtTypeId
     */
    public function setDebtTypeId($debtTypeId)
    {
        $this->debtTypeId = $debtTypeId;
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
