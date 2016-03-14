<?php

namespace AppBundle\Entity;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class AssetProperty extends Asset
{

    const OCCUPANTS_OTHER = 'other';
    const OWNED_FULLY = 'fully';
    const OWNED_PARTLY = 'partly';


    /**
     * @Assert\NotBlank(message="asset.property.address.notBlank")
     * @JMS\Type("string")
     * @var string
     */
    private $address;

    /**
     * @Assert\NotBlank(message="asset.property.address2.notBlank")
     * @JMS\Type("string")
     * @var string
     */
    private $address2;

    /**
     * @Assert\NotBlank(message="asset.property.county.notBlank")
     * @JMS\Type("string")
     * @var string
     */
    private $county;

    /**
     * @Assert\NotBlank(message="asset.property.postcode.notBlank")
     * @JMS\Type("string")
     * @var string
     */
    private $postcode;

    /**
     * @Assert\NotBlank(message="asset.property.occupants.notBlank")
     * 
     * @var string
     * 
     * @JMS\Type("string")
     */
    private $occupants;

    /**
     * @var string fully/partly
     * @Assert\NotBlank(message="asset.property.owned.notBlank")
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
     * @Assert\NotBlank(message="asset.property.isSubjectToEquityRelease.notBlank")
     * @JMS\Type("boolean")
     */
    private $isSubjectToEquityRelease;

    /**
     * @Assert\NotBlank(message="asset.property.hasMortgage.notBlank")
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
     * @Assert\NotBlank(message="asset.property.hasCharges.notBlank")
     * @var boolean
     * 
     * @JMS\Type("boolean")
     */
    private $hasCharges;

    /**
     * @Assert\NotBlank(message="asset.property.isRentedOut.notBlank")
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


    /**
     * Set address
     *
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }


    /**
     * Get address
     */
    public function getAddress()
    {
        return $this->address;
    }


    /**
     * Set postcode
     *
     * @param string $postcode
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }


    /**
     * Get address2
     *
     * @return string 
     */
    public function getAddress2()
    {
        return $this->address2;
    }


    /**
     * Set county
     *
     * @param string $county
     */
    public function setCounty($county)
    {
        $this->county = $county;

        return $this;
    }


    /**
     * Get county
     *
     * @return string 
     */
    public function getCounty()
    {
        return $this->county;
    }


    /**
     * Get postcode
     */
    public function getPostcode()
    {
        return $this->postcode;
    }


    /**
     * Set address2
     *
     * @param string $address2
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }


    public function getCountry()
    {
        return $this->country;
    }


    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }


    public function getOccupants()
    {
        return $this->occupants;
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


    public function setRentAgreementEndDate($rentAgreementEndDate)
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
        return 'property';
    }

}