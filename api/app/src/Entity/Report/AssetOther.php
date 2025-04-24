<?php

namespace App\Entity\Report;

use App\Entity\AssetInterface;
use App\Entity\Ndr\AssetOther as NdrAssetOther;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 */
class AssetOther extends Asset implements AssetInterface
{
    /**
     * @var string type of the asset
     *             Vehicles | Jewellery etc...
     *             (needs refactor into an enum, as it originally was a freetext)
     *
     *
     * @ORM\Column(name="title", type="string", length=100, nullable=true)
     */
    #[JMS\Groups(['asset'])]
    private $title;

    /**
     * @var string more info about asset
     *
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    #[JMS\Groups(['asset'])]
    private $description;

    /**
     * @var \Date
     *
     *
     *
     * @ORM\Column(name="valuation_date", type="date", nullable=true)
     */
    #[JMS\Type('DateTime')]
    #[JMS\Groups(['asset'])]
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

    
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('type')]
    #[JMS\Groups(['asset'])]
    public function getAssetType()
    {
        return 'other';
    }

    public function getType()
    {
        return 'other';
    }

    /**
     * @return bool
     */
    public function isEqual(AssetInterface $asset)
    {
        if (!($asset instanceof self) && !($asset instanceof NdrAssetOther)) {
            return false;
        }

        return $asset->getDescription() === $this->getDescription();
    }
}
