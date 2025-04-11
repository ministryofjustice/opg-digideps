<?php

namespace App\Entity\Report;

use App\Entity\Report\Traits\HasReportTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @JMS\Discriminator(field = "type", map = {
 *    "other": "App\Entity\Report\AssetOther",
 *    "property": "App\Entity\Report\AssetProperty"
 * })
 */
abstract class Asset
{
    use HasReportTrait;

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
        switch ($typeLower) {
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
     * @Assert\NotBlank(message="asset.title.notBlank", groups={"title_only"})
     *
     * @Assert\Length(max=100, maxMessage= "asset.title.maxMessage", groups={"title_only"})
     *
     * @JMS\Type("string")
     */
    private $title;

    /**
     * @Assert\NotBlank(message="asset.value.notBlank")
     *
     * @Assert\Type( type="numeric", message="asset.value.type")
     *
     * @Assert\Range(min=0, max=100000000000, notInRangeMessage = "asset.value.outOfRange")
     *
     * @Assert\NotBlank(message="asset.property.value.notBlank", groups={"property-value"})
     *
     * @Assert\Type( type="numeric", message="asset.property.value.type", groups={"property-value"})
     *
     * @Assert\Range(min=0, max=100000000000, notInRangeMessage = "asset.property.value.outOfRange", groups={"property-value"})
     *
     * @JMS\Type("string")
     */
    private $value;

    /**
     * @JMS\Type("double")
     *
     * @var float
     */
    private $valueTotal;

    /**
     * @Assert\Type(type="DateTimeInterface",message="asset.date.date")
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
        return $this->valueTotal;
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
