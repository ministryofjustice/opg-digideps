<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;


/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class AssetProperty extends Asset
{
    const OCCUPANTS_OTHER = 'other';
    const OWNED_FULLY = 'fully';
    const OWNED_PARTLY = 'partly';

    use Traits\AddressTrait;
    
    /**
     * @var string
     * @JMS\Groups({"asset"})
     * @JMS\Type("string")
     * @ORM\Column(name="occupants", type="string", length=550)
     */
    private $occupants;
    
    /**
     * @var string fully/partly
     * @JMS\Groups({"asset"})
     * @JMS\Type("string")
     * @ORM\Column(name="owned", type="string", length=15)
     */
    private $owned;
    
    /**
     * @var float 0-100
     * @JMS\Groups({"asset"})
     * @JMS\Type("float")
     * @ORM\Column(name="owned_percentage", type="decimal", precision=14, scale=2)
     */
    private $ownedPercentage;
    
    /**
     * @JMS\Groups({"asset"})
     * @JMS\Type("boolean")
     * @ORM\Column(name="is_subject_equity_rel", type="boolean")
     */
    private $isSubjectToEquityRelease;
    
    /**
     * @var boolean
     * @JMS\Groups({"asset"})
     * @JMS\Type("boolean")
     * @ORM\Column(name="has_mortgage", type="boolean")
     */
    private $hasMortgage;
    
    
    /**
     * @var boolean
     * @JMS\Groups({"asset"})
     * @JMS\Type("integer")
     * @ORM\Column(name="mortgage_outstanding", type="integer")
     */
    private $mortgageOutstandingAmount;
    
     /**
     * @var boolean
     * @JMS\Groups({"asset"})
     * @JMS\Type("boolean")
     * @ORM\Column(name="has_charges", type="boolean")
     */
    private $hasCharges;
    
    /**
     * @var boolean
     * @JMS\Groups({"asset"})
     * @JMS\Type("boolean")
     * @ORM\Column(name="is_rented_out", type="boolean")
     */
    private $isRentedOut;
    
    /**
     * @var \DateTime
     * @JMS\Groups({"asset"})
     * @JMS\Type("DateTime")
     * @ORM\Column(name="rent_agreement_end_date", type="datetime", nullable=true)
     */
    private $rentAgreementEndDate;
    
    /**
     * @var float
     * @JMS\Groups({"asset"})
     * @JMS\Type("float")
     * @ORM\Column(name="rent_income_month", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $rentIncomeMonth;
    
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
        $this->hasCharges = (boolean)$hasCharges;
        return $this;
    }

    public function setIsRentedOut($isRentedOut)
    {
        $this->isRentedOut = (boolean)$isRentedOut;
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

    
    /** 
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function deleteUnusedData()
    {
        if ($this->getIsRentedOut() === false) {
            $this->setRentAgreementEndDate(null);
            $this->setRentIncomeMonth(null);
        }
        
        if ($this->getHasMortgage() === false) {
            $this->setMortgageOutstandingAmount(null);
        }
        
        if ($this->getOwned() === self::OWNED_FULLY) {
            $this->setOwnedPercentage(null);
        }
    }

    
}