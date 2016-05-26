<?php

namespace AppBundle\Entity;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

class Debt
{
    /**
     * Hold debts type
     * 1st value = id, 2nd value = hasMoreInformation
     * @var array
     */
    public static $debtTypeIds = [
        ['care-fees', false],
        ['credit-cards', false],
        ['loans', false],
        ['other', true],
    ];

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"transactionsIn", "transactionsOut"})
     */
    private $debtTypeId;

    /**
     * @var decimal
     *
     * @JMS\Type("string")
     * @JMS\Groups({"transactionsIn", "transactionsOut"})
     * @Assert\Type(type="numeric", message="account.moneyInOut.amount.notNumeric", groups={"transactions"})
     * @Assert\Range(min=0, max=10000000000, minMessage = "account.moneyInOut.amount.minMessage", maxMessage = "account.moneyInOut.amount.maxMessage", groups={"transactions"})
     */
    private $amount;


    /**
     * @var string
     * @JMS\Groups({"transactionsIn", "transactionsOut"})
     * @JMS\Type("boolean")
     */
    private $hasMoreDetails;

    /**
     * @var string
     * @JMS\Groups({"transactionsIn", "transactionsOut"})
     * @JMS\Type("string")
     */
    private $moreDetails;

    /**
     * Debt constructor.
     * @param $debtTypeId
     * @param decimal $amount
     * @param string $hasMoreDetails
     * @param string $moreDetails
     */
    public function __construct($debtTypeId, $amount, $hasMoreDetails, $moreDetails)
    {
        $this->debtTypeId = $debtTypeId;
        $this->amount = $amount;
        $this->hasMoreDetails = $hasMoreDetails;
        $this->moreDetails = $moreDetails;
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
