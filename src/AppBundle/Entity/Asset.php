<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\Discriminator(field = "type", map = {
 *    "other": "AppBundle\Entity\AssetOther",
 *    "property": "AppBundle\Entity\AssetProperty"
 * })
 */
abstract class Asset
{
    /**
     * @JMS\Exclude
     */
    protected $type;

    use Traits\HasReportTrait;

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
     * @return Asset
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

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValuationDate($valuationDate)
    {
        $this->valuationDate = $valuationDate;

        return $this;
    }

    public function getValuationDate()
    {
        return $this->valuationDate;
    }
}
