<?php

namespace App\Entity\Ndr;

use App\Entity\Ndr\Traits\HasNdrTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @JMS\Discriminator(field = "type", map = {
 *    "other": "App\Entity\Ndr\AssetOther",
 *    "property": "App\Entity\Ndr\AssetProperty"
 * })
 */
abstract class Asset
{
    use HasNdrTrait;

    /**
     * @JMS\Exclude
     */
    protected $type;

    /**
     * @param string $type
     *
     * @return Asset instance
     */
    public static function factory($type)
    {
        $typeLower = is_null($type) ? '' : strtolower($type);
        switch (strtolower($typeLower)) {
            case 'property':
                return new AssetProperty();
            default:
                $other = new AssetOther();
                $other->setTitle($typeLower);

                return $other;
        }
    }

    /**
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="ndr.asset.title.notBlank", groups={"title_only"})
     *
     * @Assert\Length(max=100, maxMessage= "ndr.asset.title.maxMessage", groups={"title_only"})
     *
     * @JMS\Type("string")
     */
    private $title;

    /**
     * @Assert\NotBlank(message="ndr.asset.value.notBlank")
     *
     * @Assert\Type( type="numeric", message="ndr.asset.value.type")
     *
     * @Assert\Range(min=0, max=100000000000, maxMessage = "ndr.asset.value.outOfRange")
     *
     * @Assert\NotBlank(message="ndr.asset.property.value.notBlank", groups={"property-value"})
     *
     * @Assert\Type( type="numeric", message="ndr.asset.property.value.type", groups={"property-value"})
     *
     * @Assert\Range(min=0, max=100000000000, maxMessage = "ndr.asset.property.value.outOfRange", groups={"property-value"})
     *
     * @JMS\Type("string")
     */
    private $value;

    /**
     * @Assert\Type(type="DateTimeInterface",message="ndr.asset.date.date")
     *
     * @JMS\Type("DateTime")
     */
    private $valuationDate;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return static
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return static
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return float|null
     */
    public function getValueTotal()
    {
        return $this->value;
    }

    /**
     * @return static
     */
    public function setValuationDate($valuationDate)
    {
        $this->valuationDate = $valuationDate;

        return $this;
    }

    public function getValuationDate()
    {
        return $this->valuationDate;
    }

    /**
     * Get name of the template (Asset/list-items/_<template>.html.twig) used to render the partial in the list view.
     *
     * @return string
     */
    abstract public function getListTemplateName();

    /**
     * Get an unique human readable ID in order to identify the item in the list based on its content.
     * Needed by functional testing.
     *
     * @return string
     */
    abstract public function getBehatIdentifier();
}
