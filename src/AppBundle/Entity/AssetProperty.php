<?php

namespace AppBundle\Entity;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class AssetProperty extends Asset
{

    const OCCUPANTS_OTHER = 'other';
    const OWNED_FULLY = 'fully';
    const OWNED_PARTLY = 'partly';

    use Traits\AddressTrait;

     /**
     * @JMS\Type("string")
     * @var string
     */
    private $type = 'property';
    
    /**
     * @var string
     * 
     * @JMS\Type("string")
     */
    private $occupants;

    /**
     * @var string
     * 
     * @JMS\Type("string")
     */
    private $occupantsInfo;

    /**
     * @var string fully/partly
     * 
     * @JMS\Type("string")
     */
    private $owned;

    /**
     * @var float 0-100
     * 
     * @JMS\Type("float")
     */
    private $ownedPercentage;

    /**
     * @var 
     * @JMS\Type("boolean")
     */
    private $isSubjectToEquityRelease;

    /**
     * @var boolean
     * @JMS\Type("boolean")
     */
    private $hasMortgage;

    /**
     * @var boolean
     * @JMS\Type("integer")
     */
    private $mortgageOutstandingAmount;

    /**
     * @var boolean
     * 
     * @JMS\Type("boolean")
     */
    private $hasCharges;

    /**
     * @var boolean
     * 
     * @JMS\Type("boolean")
     */
    private $isRentedOut;

    /**
     * @var \DateTime
     * @JMS\Type("DateTime")
     */
    private $rentAgreementEndDate;

    /**
     * @var float
     * @JMS\Type("float")
     */
    private $rentIncomeMonth;


    public function getOccupants()
    {
        return $this->occupants;
    }


    public function getOccupantsInfo()
    {
        return $this->occupantsInfo;
    }


    public function getOwned()
    {
        return $this->owned;
    }


    public function getOwnedPercentage()
    {
        return $this->ownedPercentage;
    }


    public function getIsSubjectToEquityRelease()
    {
        return $this->isSubjectToEquityRelease;
    }


    public function getHasMortgage()
    {
        return $this->hasMortgage;
    }


    public function getMortgageOutstandingAmount()
    {
        return $this->mortgageOutstandingAmount;
    }


    public function getHasCharges()
    {
        return $this->hasCharges;
    }


    public function getIsRentedOut()
    {
        return $this->isRentedOut;
    }


    public function getRentAgreementEndDate()
    {
        return $this->rentAgreementEndDate;
    }


    public function getRentIncomeMonth()
    {
        return $this->rentIncomeMonth;
    }


    public function setOccupants($occupants)
    {
        $this->occupants = $occupants;
        return $this;
    }


    public function setOccupantsInfo($occupantsInfo)
    {
        $this->occupantsInfo = $occupantsInfo;
        return $this;
    }


    public function setOwned($owned)
    {
        if (!in_array($owned, [self::OWNED_FULLY, self::OWNED_PARTLY])) {
            throw new \InvalidArgumentException(__METHOD__ . "Invalid owned type [$owned]");
        }

        $this->owned = $owned;
        return $this;
    }


    public function setOwnedPercentage($ownedPercentage)
    {
        $this->ownedPercentage = $ownedPercentage;
        return $this;
    }


    public function setIsSubjectToEquityRelease($isSubjectToEquityRelease)
    {
        $this->isSubjectToEquityRelease = $isSubjectToEquityRelease;
        return $this;
    }


    public function setHasMortgage($hasMortgage)
    {
        $this->hasMortgage = $hasMortgage;
        return $this;
    }


    public function setMortgageOutstandingAmount($mortgageOutstandingAmount)
    {
        $this->mortgageOutstandingAmount = $mortgageOutstandingAmount;
        return $this;
    }


    public function setHasCharges($hasCharges)
    {
        $this->hasCharges = (boolean) $hasCharges;
        return $this;
    }


    public function setIsRentedOut($isRentedOut)
    {
        $this->isRentedOut = (boolean) $isRentedOut;
        return $this;
    }


    public function setRentAgreementEndDate(\DateTime $rentAgreementEndDate)
    {
        $this->rentAgreementEndDate = $rentAgreementEndDate;
        return $this;
    }


    public function setRentIncomeMonth($rentIncomeMonth)
    {
        $this->rentIncomeMonth = $rentIncomeMonth;
        return $this;
    }
    
    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }



}