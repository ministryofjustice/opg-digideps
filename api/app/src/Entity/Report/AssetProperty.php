<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

#[ORM\Entity, ORM\HasLifecycleCallbacks]
class AssetProperty extends Asset
{
    public const string OCCUPANTS_OTHER = 'other';
    public const string OWNED_FULLY = 'fully';
    public const string OWNED_PARTLY = 'partly';

    #[JMS\Type('string')]
    #[JMS\Groups(['asset'])]
    #[ORM\Column(name: 'address', type: 'string', length: 200, nullable: true)]
    private ?string $address = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['asset'])]
    #[ORM\Column(name: 'address2', type: 'string', length: 200, nullable: true)]
    private ?string $address2 = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['asset'])]
    #[ORM\Column(name: 'county', type: 'string', length: 75, nullable: true)]
    private ?string $county = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['asset'])]
    #[ORM\Column(name: 'postcode', type: 'string', length: 10, nullable: true)]
    private ?string $postcode = null;

    #[JMS\Groups(['asset'])]
    #[JMS\Type('string')]
    #[ORM\Column(name: 'occupants', type: 'string', length: 550, nullable: true)]
    private ?string $occupants = null;

    /**
     * fully/partly
     */
    #[JMS\Groups(['asset'])]
    #[JMS\Type('string')]
    #[ORM\Column(name: 'owned', type: 'string', length: 15, nullable: true)]
    private ?string $owned = null;

    /**
     * 0-100
     */
    #[JMS\Groups(['asset'])]
    #[JMS\Type('float')]
    #[ORM\Column(name: 'owned_percentage', type: 'decimal', precision: 14, scale: 2, nullable: true)]
    private ?string $ownedPercentage = null;

    #[JMS\Groups(['asset'])]
    #[JMS\Type('string')]
    #[ORM\Column(name: 'is_subject_equity_rel', type: 'string', length: 4, nullable: true)]
    private ?string $isSubjectToEquityRelease = null;

    #[JMS\Groups(['asset'])]
    #[JMS\Type('string')]
    #[ORM\Column(name: 'has_mortgage', type: 'string', length: 4, nullable: true)]
    private ?string $hasMortgage = null;

    #[JMS\Groups(['asset'])]
    #[JMS\Type('integer')]
    #[ORM\Column(name: 'mortgage_outstanding', type: 'decimal', precision: 14, scale: 2, nullable: true)]
    private ?string $mortgageOutstandingAmount = null;

    #[JMS\Groups(['asset'])]
    #[JMS\Type('string')]
    #[ORM\Column(name: 'has_charges', type: 'string', length: 4, nullable: true)]
    private ?string $hasCharges = null;

    #[JMS\Groups(['asset'])]
    #[JMS\Type('string')]
    #[ORM\Column(name: 'is_rented_out', type: 'string', length: 4, nullable: true)]
    private ?string $isRentedOut = null;

    #[JMS\Groups(['asset'])]
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[ORM\Column(name: 'rent_agreement_end_date', type: 'datetime', nullable: true)]
    private ?\DateTime $rentAgreementEndDate = null;

    #[JMS\Groups(['asset'])]
    #[JMS\Type('float')]
    #[ORM\Column(name: 'rent_income_month', type: 'decimal', precision: 14, scale: 2, nullable: true)]
    private ?string $rentIncomeMonth = '0.0';

    protected function getType(): string
    {
        return 'property';
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
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

    public function getOccupants(): ?string
    {
        return $this->occupants;
    }

    public function getOwned(): string
    {
        return $this->owned ?? AssetProperty::OWNED_FULLY;
    }

    public function getOwnedPercentage(): ?string
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

    public function getMortgageOutstandingAmount(): ?string
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

    public function getRentIncomeMonth(): ?string
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

    public function setOwnedPercentage(null|int|float|string $ownedPercentage): static
    {
        $this->ownedPercentage = $ownedPercentage === null ? null : (string)$ownedPercentage;

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

    public function setMortgageOutstandingAmount(float|int|string|null $mortgageOutstandingAmount): static
    {
        $this->mortgageOutstandingAmount = $mortgageOutstandingAmount !== null ? (string)$mortgageOutstandingAmount : '0.0';

        return $this;
    }

    public function setHasCharges(?string $hasCharges): static
    {
        $this->hasCharges = $hasCharges;

        return $this;
    }

    public function setIsRentedOut(?string $isRentedOut): static
    {
        $this->isRentedOut = $isRentedOut;

        return $this;
    }

    public function setRentAgreementEndDate(?\DateTime $rentAgreementEndDate): static
    {
        $this->rentAgreementEndDate = $rentAgreementEndDate;

        return $this;
    }

    public function setRentIncomeMonth(int|float|string|null $rentIncomeMonth): static
    {
        $this->rentIncomeMonth = $rentIncomeMonth !== null ? (string)$rentIncomeMonth : null;

        return $this;
    }

    public function getValueTotal(): ?float
    {
        if ($this->getOwned() == self::OWNED_PARTLY) {
            return floatval($this->getValue()) * floatval($this->getOwnedPercentage()) / 100.0;
        }

        return parent::getValueTotal();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function deleteUnusedData(): void
    {
        if ($this->getIsRentedOut() === 'no') {
            $this->setRentAgreementEndDate(null);
            $this->setRentIncomeMonth(null);
        }

        if ($this->getHasMortgage() === 'no') {
            $this->setMortgageOutstandingAmount(null);
        }

        if ($this->getOwned() === self::OWNED_FULLY) {
            $this->setOwnedPercentage(0);
        }
    }

    public function isEqual(Asset $asset): bool
    {
        if ($asset instanceof AssetProperty) {
            return $asset->getAddress() === $this->getAddress()
                && $asset->getAddress2() === $this->getAddress2()
                && $asset->getPostcode() === $this->getPostcode();
        }
        return false;
    }

    #[ORM\PreRemove]
    public function onPreRemove(PreRemoveEventArgs $_): void
    {
        if ($this->getReport()->getAssets()->count() === 1) {
            $this->getReport()->setNoAssetToAdd(null);
        }
    }
}
