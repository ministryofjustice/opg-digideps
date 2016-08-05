<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Traits\HasReportTrait;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\Discriminator(field = "type", map = {
 *    "other": "AppBundle\Entity\Report\AssetOther",
 *    "property": "AppBundle\Entity\Report\AssetProperty"
 * })
 */
abstract class Asset
{
    /**
     * @JMS\Exclude
     */
    protected $type;

    use HasReportTrait;

    /**
     * @param string $type
     *
     * @return Asset instance
     */
    public static function factory($type)
    {
        switch (strtolower($type)) {
            case 'property':
                return new AssetProperty();
            default:
                $other = new AssetOther();
                $other->setTitle($type);

                return $other;
        }
    }

    /**
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="asset.title.notBlank", groups={"title_only"})
     * @Assert\Length(max=100, maxMessage= "asset.title.maxMessage", groups={"title_only"})
     * @JMS\Type("string")
     */
    private $title;

    /**
     * @Assert\NotBlank(message="asset.value.notBlank")
     * @Assert\Type( type="numeric", message="asset.value.type")
     * @Assert\Range(max=100000000000, maxMessage = "asset.value.outOfRange")
     * 
     * @Assert\NotBlank(message="asset.property.value.notBlank", groups={"property"})
     * @Assert\Type( type="numeric", message="asset.property.value.type", groups={"property"})
     * @Assert\Range(max=10000000000, maxMessage = "asset.property.value.outOfRange", groups={"property"})
     * 
     * @JMS\Type("string")
     */
    private $value;

    /**
     * @Assert\Date(message="asset.date.date")
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
     * @param $value
     * @return static
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $valuationDate
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
