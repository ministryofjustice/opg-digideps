<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Report\Traits\HasReportTrait;
use Symfony\Component\Validator\Constraints as Assert;

#[JMS\Discriminator(field: 'type', map: ['other' => 'OPG\Digideps\Frontend\Entity\Report\AssetOther', 'property' => 'OPG\Digideps\Frontend\Entity\Report\AssetProperty'])]
abstract class Asset
{
    use HasReportTrait;

    #[JMS\Type('integer')]
    private ?int $id = null;

    #[JMS\Exclude]
    protected ?string $type = null;

    /**
     * @JMS\Type("DateTime")
     * @phpstan-ignore property.unusedType
     */
    private ?\DateTime $createdAt = null;

    public static function factory(?string $type): Asset
    {
        $typeLower = is_null($type) ? '' : strtolower($type);
        switch ($typeLower) {
            case 'property':
                return new AssetProperty();
            default:
                $other = new AssetOther();
                $other->setTitle($typeLower);
                return $other;
        }
    }

    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'asset.title.notBlank', groups: ['title_only'])]
    #[Assert\Length(max: 100, maxMessage: 'asset.title.maxMessage', groups: ['title_only'])]
    private ?string $title = null;

    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'asset.value.notBlank')]
    #[Assert\Type(type: 'numeric', message: 'asset.value.type')]
    #[Assert\Range(notInRangeMessage: 'asset.value.outOfRange', min: 0, max: 100000000000)]
    #[Assert\NotBlank(message: 'asset.property.value.notBlank', groups: ['property-value'])]
    #[Assert\Type(type: 'numeric', message: 'asset.property.value.type', groups: ['property-value'])]
    #[Assert\Range(notInRangeMessage: 'asset.property.value.outOfRange', min: 0, max: 100000000000, groups: ['property-value'])]
    private ?string $value = null;

    #[JMS\Type('double')]
    /** @phpstan-ignore property.unusedType */
    private ?float $valueTotal = null;

    #[JMS\Type('DateTime')]
    #[Assert\Type(type: 'DateTime', message: 'asset.date.date')]
    protected ?\DateTime $valuationDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getValueTotal(): ?float
    {
        return $this->valueTotal;
    }

    public function setValuationDate(?\DateTime $valuationDate): static
    {
        $this->valuationDate = $valuationDate;

        return $this;
    }

    public function getValuationDate(): ?\DateTime
    {
        return $this->valuationDate;
    }

    /**
     * Get name of the template (Asset/list-items/_<template>.html.twig) used to render the partial in the list view.
     */
    abstract public function getListTemplateName(): string;

    /**
     * Get an unique human-readable ID in order to identify the item in the list based on its content.
     * Needed by functional testing.
     */
    abstract public function getBehatIdentifier(): string;

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

}
