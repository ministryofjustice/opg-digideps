<?php

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class AssetProperty extends Asset
{
    public const string OCCUPANTS_OTHER = 'other';
    public const string OWNED_FULLY = 'fully';
    public const string OWNED_PARTLY = 'partly';

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'asset.property.address.notBlank', groups: ['property-address'])]
    #[Assert\Length(max: 200, maxMessage: 'asset.property.address.maxMessage', groups: ['property-address'])]
    private $address;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[Assert\Length(max: 200, maxMessage: 'asset.property.address.maxMessage', groups: ['property-address'])]
    private $address2;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[Assert\Length(max: 75, maxMessage: 'asset.property.county.maxMessage', groups: ['property-address'])]
    private $county;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'asset.property.postcode.notBlank', groups: ['property-address'])]
    #[Assert\Length(max: 10, maxMessage: 'asset.property.postcode.maxMessage', groups: ['property-address'])]
    private $postcode;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'asset.property.occupants.notBlank', groups: ['property-occupants'])]
    #[Assert\Length(max: 550, maxMessage: 'asset.property.occupants.maxMessage', groups: ['property-occupants'])]
    private $occupants;

    /**
     * @var string fully/partly
     */
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'asset.property.owned.notBlank', groups: ['property-owned'])]
    private $owned;

    /**
     * @var float 0-100
     */
    #[JMS\Type('float')]
    #[Assert\NotBlank(message: 'asset.property.ownedPercentage.notBlank', groups: ['property-owned-partly'])]
    #[Assert\Range(min: 1, max: 100, notInRangeMessage: 'asset.property.ownedPercentage.type', groups: ['property-owned-partly'])]
    private $ownedPercentage;

    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'asset.property.isSubjectToEquityRelease.notBlank', groups: ['property-subject-equity-release'])]
    private $isSubjectToEquityRelease;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'asset.property.hasMortgage.notBlank', groups: ['property-mortgage'])]
    private $hasMortgage;

    /**
     * @var string
     */
    #[JMS\Type('integer')]
    #[Assert\NotBlank(message: 'asset.property.mortgageOutstandingAmount.notBlank', groups: ['property-mortgage-outstanding-amount'])]
    #[Assert\Type(type: 'numeric', message: 'asset.property.mortgageOutstandingAmount.type', groups: ['property-mortgage-outstanding-amount'])]
    #[Assert\Range(min: 0, max: 100000000000, notInRangeMessage: 'asset.property.mortgageOutstandingAmount.outOfRange', groups: ['property-mortgage-outstanding-amount'])]
    private $mortgageOutstandingAmount;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'asset.property.hasCharges.notBlank', groups: ['property-has-charges'])]
    private $hasCharges;

    /**
     *
     * @var string
     *
     */
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'asset.property.isRentedOut.notBlank', groups: ['property-rented-out'])]
    private $isRentedOut;

    /**
     * @var \DateTime
     */
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[Assert\NotBlank(message: 'asset.property.rentAgreementEndDate.notBlank', groups: ['property-rent-agree-date'])]
    private $rentAgreementEndDate;

    /**
     * @var float
     */
    #[JMS\Type('float')]
    #[Assert\NotBlank(message: 'asset.property.rentIncomeMonth.notBlank', groups: ['property-rent-income-month'])]
    #[Assert\Type(type: 'numeric', message: 'asset.property.rentIncomeMonth.type', groups: ['property-rent-income-month'])]
    #[Assert\Range(min: 0, max: 100000000000, notInRangeMessage: 'asset.property.rentIncomeMonth.outOfRange', groups: ['property-rent-income-month'])]
    private $rentIncomeMonth;

    /**
     * Set address.
     *
     * @param string $address
     */
    public function setAddress($address): static
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
    public function setPostcode($postcode): static
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
    public function setCounty($county): static
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
    public function setAddress2($address2): static
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * Get address.
     * @return String[]
     */
    public function getAddressValidLines(): array
    {
        return array_filter([$this->address, $this->address2, $this->county, $this->postcode]);
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

    public function setOccupants($occupants): static
    {
        $this->occupants = $occupants;

        return $this;
    }

    public function setOwned($owned): static
    {
        if (!in_array($owned, [self::OWNED_FULLY, self::OWNED_PARTLY])) {
            throw new \InvalidArgumentException(__METHOD__ . "Invalid owned type [$owned]");
        }

        $this->owned = $owned;

        return $this;
    }

    public function setOwnedPercentage($ownedPercentage): static
    {
        $this->ownedPercentage = $ownedPercentage;

        return $this;
    }

    public function setIsSubjectToEquityRelease($isSubjectToEquityRelease): static
    {
        $this->isSubjectToEquityRelease = $isSubjectToEquityRelease;

        return $this;
    }

    public function setHasMortgage($hasMortgage): static
    {
        $this->hasMortgage = $hasMortgage;

        return $this;
    }

    public function setMortgageOutstandingAmount($mortgageOutstandingAmount): static
    {
        $this->mortgageOutstandingAmount = $mortgageOutstandingAmount;

        return $this;
    }

    public function setHasCharges($hasCharges): static
    {
        $this->hasCharges = $hasCharges;

        return $this;
    }

    public function setIsRentedOut($isRentedOut)
    {
        $this->isRentedOut = $isRentedOut;

        return $this;
    }

    public function setRentAgreementEndDate($rentAgreementEndDate): static
    {
        $this->rentAgreementEndDate = $rentAgreementEndDate;

        return $this;
    }

    public function setRentIncomeMonth($rentIncomeMonth): static
    {
        $this->rentIncomeMonth = $rentIncomeMonth;

        return $this;
    }

    public function getType(): string
    {
        return 'property';
    }

    public function getListTemplateName(): string
    {
        return 'property';
    }

    public function getBehatIdentifier()
    {
        return $this->getAddress() . ' ' . $this->getPostcode();
    }
}
