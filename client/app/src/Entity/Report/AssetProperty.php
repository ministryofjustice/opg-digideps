<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class AssetProperty extends Asset
{
    public const string OWNED_FULLY = 'fully';
    public const string OWNED_PARTLY = 'partly';

    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'asset.property.address.notBlank', groups: ['property-address'])]
    #[Assert\Length(max: 200, maxMessage: 'asset.property.address.maxMessage', groups: ['property-address'])]
    private ?string $address = null;

    #[JMS\Type('string')]
    #[Assert\Length(max: 200, maxMessage: 'asset.property.address.maxMessage', groups: ['property-address'])]
    private ?string $address2 = null;

    #[JMS\Type('string')]
    #[Assert\Length(max: 75, maxMessage: 'asset.property.county.maxMessage', groups: ['property-address'])]
    private ?string $county = null;

    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'asset.property.postcode.notBlank', groups: ['property-address'])]
    #[Assert\Length(max: 10, maxMessage: 'asset.property.postcode.maxMessage', groups: ['property-address'])]
    private ?string $postcode = null;

    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'asset.property.occupants.notBlank', groups: ['property-occupants'])]
    #[Assert\Length(max: 550, maxMessage: 'asset.property.occupants.maxMessage', groups: ['property-occupants'])]
    private ?string $occupants = null;

    /**
     * @var ?string 'fully'|'partly'|null
     */
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'asset.property.owned.notBlank', groups: ['property-owned'])]
    private ?string $owned = null;

    /**
     * @var ?float 0-100|null
     */
    #[JMS\Type('float')]
    #[Assert\NotBlank(message: 'asset.property.ownedPercentage.notBlank', groups: ['property-owned-partly'])]
    #[Assert\Range(notInRangeMessage: 'asset.property.ownedPercentage.type', min: 1, max: 100, groups: ['property-owned-partly'])]
    private ?float $ownedPercentage = null;

    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'asset.property.isSubjectToEquityRelease.notBlank', groups: ['property-subject-equity-release'])]
    private ?string $isSubjectToEquityRelease = null;

    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'asset.property.hasMortgage.notBlank', groups: ['property-mortgage'])]
    private ?string $hasMortgage = null;

    #[JMS\Type('integer')]
    #[Assert\NotBlank(message: 'asset.property.mortgageOutstandingAmount.notBlank', groups: ['property-mortgage-outstanding-amount'])]
    #[Assert\Type(type: 'numeric', message: 'asset.property.mortgageOutstandingAmount.type', groups: ['property-mortgage-outstanding-amount'])]
    #[Assert\Range(notInRangeMessage: 'asset.property.mortgageOutstandingAmount.outOfRange', min: 0, max: 100000000000, groups: ['property-mortgage-outstanding-amount'])]
    private ?int $mortgageOutstandingAmount = null;

    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'asset.property.hasCharges.notBlank', groups: ['property-has-charges'])]
    private ?string $hasCharges = null;

    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'asset.property.isRentedOut.notBlank', groups: ['property-rented-out'])]
    private ?string $isRentedOut = null;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[Assert\NotBlank(message: 'asset.property.rentAgreementEndDate.notBlank', groups: ['property-rent-agree-date'])]
    private ?\DateTime $rentAgreementEndDate = null;

    #[JMS\Type('float')]
    #[Assert\NotBlank(message: 'asset.property.rentIncomeMonth.notBlank', groups: ['property-rent-income-month'])]
    #[Assert\Type(type: 'numeric', message: 'asset.property.rentIncomeMonth.type', groups: ['property-rent-income-month'])]
    #[Assert\Range(notInRangeMessage: 'asset.property.rentIncomeMonth.outOfRange', min: 0, max: 100000000000, groups: ['property-rent-income-month'])]
    private ?float $rentIncomeMonth = null;

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function setPostcode(?string $postcode): static
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function setCounty(?string $county): static
    {
        $this->county = $county;

        return $this;
    }

    public function getCounty(): ?string
    {
        return $this->county;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setAddress2(?string $address2): static
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getAddressValidLines(): array
    {
        return array_filter([$this->address, $this->address2, $this->county, $this->postcode]);
    }

    public function getOccupants(): ?string
    {
        return $this->occupants;
    }

    public function getOwned(): ?string
    {
        return $this->owned;
    }

    public function getOwnedPercentage(): ?float
    {
        return $this->ownedPercentage;
    }

    public function getIsSubjectToEquityRelease(): ?string
    {
        return $this->isSubjectToEquityRelease;
    }

    public function getHasMortgage(): ?string
    {
        return $this->hasMortgage;
    }

    public function getMortgageOutstandingAmount(): ?int
    {
        return $this->mortgageOutstandingAmount;
    }

    public function getHasCharges(): ?string
    {
        return $this->hasCharges;
    }

    public function getIsRentedOut(): ?string
    {
        return $this->isRentedOut;
    }

    public function getRentAgreementEndDate(): ?\DateTime
    {
        return $this->rentAgreementEndDate;
    }

    public function getRentIncomeMonth(): ?float
    {
        return $this->rentIncomeMonth;
    }

    public function setOccupants(?string $occupants): static
    {
        $this->occupants = $occupants;

        return $this;
    }

    public function setOwned(string $owned): static
    {
        if (!in_array($owned, [self::OWNED_FULLY, self::OWNED_PARTLY])) {
            throw new \InvalidArgumentException(__METHOD__ . "Invalid owned type [$owned]");
        }

        $this->owned = $owned;

        return $this;
    }

    public function setOwnedPercentage(?float $ownedPercentage): static
    {
        $this->ownedPercentage = $ownedPercentage;

        return $this;
    }

    public function setIsSubjectToEquityRelease(?string $isSubjectToEquityRelease): static
    {
        $this->isSubjectToEquityRelease = $isSubjectToEquityRelease;

        return $this;
    }

    public function setHasMortgage(?string $hasMortgage): static
    {
        $this->hasMortgage = $hasMortgage;

        return $this;
    }

    public function setMortgageOutstandingAmount(?int $mortgageOutstandingAmount): static
    {
        $this->mortgageOutstandingAmount = $mortgageOutstandingAmount;

        return $this;
    }

    public function setHasCharges(?string $hasCharges): static
    {
        $this->hasCharges = $hasCharges;

        return $this;
    }

    public function setIsRentedOut(?string $isRentedOut)
    {
        $this->isRentedOut = $isRentedOut;

        return $this;
    }

    public function setRentAgreementEndDate(?\DateTime $rentAgreementEndDate): static
    {
        $this->rentAgreementEndDate = $rentAgreementEndDate;

        return $this;
    }

    public function setRentIncomeMonth(?float $rentIncomeMonth): static
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

    public function getBehatIdentifier(): string
    {
        return $this->getAddress() . ' ' . $this->getPostcode();
    }
}
