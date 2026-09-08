<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Report\Traits\HasReportTrait;
use Symfony\Component\Validator\Constraints as Assert;

class Contact
{
    use HasReportTrait;

    #[JMS\Type('integer')]
    #[JMS\Groups(['contact'])]
    private int $id;

    #[JMS\SerializedName('contact_name')]
    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    #[Assert\NotBlank(message: 'contact.name.notBlank')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'contact.name.minMessage', maxMessage: 'contact.name.maxMessage')]
    private ?string $contactName = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    #[Assert\Length(max: 200, maxMessage: 'contact.address.maxMessage')]
    private ?string $address = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    #[Assert\Length(max: 200, maxMessage: 'contact.address.maxMessage')]
    private ?string $address2 = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    #[Assert\Length(max: 200, maxMessage: 'contact.address.maxMessage')]
    private ?string $county = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    #[Assert\Length(max: 10, maxMessage: 'contact.postcode.maxMessage')]
    private ?string $postcode = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    private ?string $country = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    #[Assert\Length(min: 6, minMessage: 'contact.explanation.length')]
    #[Assert\NotBlank(message: 'contact.explanation.notBlank')]
    private ?string $explanation = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    #[Assert\NotBlank(message: 'contact.relationship.notBlank')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'contact.relationship.minMessage', maxMessage: 'contact.relationship.maxMessage')]
    private ?string $relationship = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    #[Assert\Length(max: 20, maxMessage: 'contact.phone.maxMessage')]
    private ?string $phone = null;

    #[JMS\Type('DateTime')]
    #[JMS\Groups(['created-at'])]
    /** @phpstan-ignore property.unusedType */
    private ?\DateTime $createdAt = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getContactName(): ?string
    {
        return $this->contactName;
    }

    public function setContactName(?string $contactName): static
    {
        $this->contactName = $contactName;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function setAddress2(?string $address2): static
    {
        $this->address2 = $address2;
        return $this;
    }

    public function getCounty(): ?string
    {
        return $this->county;
    }

    public function setCounty(?string $county): static
    {
        $this->county = $county;
        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(?string $postcode): static
    {
        $this->postcode = $postcode;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function getExplanation(): ?string
    {
        return $this->explanation;
    }

    public function setExplanation(?string $explanation): static
    {
        $this->explanation = $explanation;
        return $this;
    }

    public function getRelationship(): ?string
    {
        return $this->relationship;
    }

    public function setRelationship(?string $relationship): static
    {
        $this->relationship = $relationship;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }
}
