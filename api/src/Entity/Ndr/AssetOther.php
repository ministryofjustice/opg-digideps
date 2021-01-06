<?php

namespace AppBundle\Entity\Ndr;

use AppBundle\Entity\AssetInterface;
use AppBundle\Entity\Report\AssetOther as ReportAssetOther;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 */
class AssetOther extends Asset implements AssetInterface
{
    /**
     * @var string
     * @JMS\Groups({"ndr-asset"})
     * @ORM\Column(name="title", type="string", length=100, nullable=true)
     */
    private $title;

    /**
     * @var string
     * @JMS\Groups({"ndr-asset"})
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var \Date
     * @JMS\Type("DateTime")
     * @JMS\Groups({"ndr-asset"})
     * @ORM\Column(name="valuation_date", type="date", nullable=true)
     */
    private $valuationDate;

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Asset
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set valuationDate.
     *
     * @param \DateTime $valuationDate
     *
     * @return Asset
     */
    public function setValuationDate($valuationDate)
    {
        $this->valuationDate = $valuationDate;

        return $this;
    }

    /**
     * Get valuationDate.
     *
     * @return \DateTime
     */
    public function getValuationDate()
    {
        return $this->valuationDate;
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

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("type")
     * @JMS\Groups({"ndr-asset"})
     */
    public function getAssetType()
    {
        return 'other';
    }

    public function getType()
    {
        return 'other';
    }

    /**
     * @param AssetInterface $asset
     * @return bool
     */
    public function isEqual(AssetInterface $asset)
    {
        if (!($asset instanceof self) && !($asset instanceof ReportAssetOther)) {
            return false;
        }

        return $asset->getDescription() === $this->getDescription();
    }
}
