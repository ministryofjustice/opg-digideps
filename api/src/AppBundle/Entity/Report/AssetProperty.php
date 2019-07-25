<?php

namespace AppBundle\Entity\Report;

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

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"asset"})
     *
     * @ORM\Column(name="address", type="string", length=200, nullable=true)
     */
    private $address;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"asset"})
     *
     * @ORM\Column(name="address2", type="string", length=200, nullable=true)
     */
    private $address2;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"asset"})
     *
     * @ORM\Column(name="county", type="string", length=75, nullable=true)
     */
    private $county;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"asset"})
     *
     * @var string
     *
     * @ORM\Column(name="postcode", type="string", length=10, nullable=true)
     */
    private $postcode;

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
     * @JMS\Type("string")
     * @ORM\Column(name="is_subject_equity_rel", type="string", length=4)
     */
    private $isSubjectToEquityRelease;

    /**
     * @var string
     * @JMS\Groups({"asset"})
     * @JMS\Type("string")
     * @ORM\Column(name="has_mortgage",  type="string", length=4)
     */
    private $hasMortgage;

    /**
     * @var float
     * @JMS\Groups({"asset"})
     * @JMS\Type("integer")
     * @ORM\Column(name="mortgage_outstanding", type="decimal", precision=14, scale=2)
     */
    private $mortgageOutstandingAmount;

    /**
     * @var string
     * @JMS\Groups({"asset"})
     * @JMS\Type("string")
     * @ORM\Column(name="has_charges",  type="string", length=4)
     */
    private $hasCharges;

    /**
     * @var string
     * @JMS\Groups({"asset"})
     * @JMS\Type("string")
     * @ORM\Column(name="is_rented_out",  type="string", length=4)
     */
    private $isRentedOut;

    /**
     * @var \DateTime
     * @JMS\Groups({"asset"})
     * @JMS\Type("DateTime<'Y-m-d'>")
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

    /**
     * Set address.
     *
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address.
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set postcode.
     *
     * @param string $postcode
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * Get address2.
     *
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * Set county.
     *
     * @param string $county
     */
    public function setCounty($county)
    {
        $this->county = $county;

        return $this;
    }

    /**
     * Get county.
     *
     * @return string
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * Get postcode.
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * Set address2.
     *
     * @param string $address2
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

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
        $this->hasCharges = $hasCharges;

        return $this;
    }

    public function setIsRentedOut($isRentedOut)
    {
        $this->isRentedOut = $isRentedOut;

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

    /**
     * @return float|null
     */
    public function getValueTotal()
    {
        if ($this->getOwned() == self::OWNED_PARTLY) {
            return $this->getValue() * $this->getOwnedPercentage() / 100;
        }

        return parent::getValueTotal();
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function deleteUnusedData()
    {
        if ($this->getIsRentedOut() === 'no') {
            $this->setRentAgreementEndDate(null);
            $this->setRentIncomeMonth(null);
        }

        if ($this->getHasMortgage() ===  'no') {
            $this->setMortgageOutstandingAmount(null);
        }

        if ($this->getOwned() === self::OWNED_FULLY) {
            $this->setOwnedPercentage(null);
        }
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("type")
     * @JMS\Groups({"asset"})
     */
    public function getAssetType()
    {
        return 'property';
    }

    public function getType()
    {
        return 'property';
    }
}
