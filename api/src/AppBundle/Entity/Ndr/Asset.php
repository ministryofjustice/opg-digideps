<?php

namespace AppBundle\Entity\Ndr;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Asset.
 *
 * @ORM\Table(name="odr_asset")
 * @ORM\Entity()
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *      "property"  = "AppBundle\Entity\Ndr\AssetProperty",
 *      "other"     = "AppBundle\Entity\Ndr\AssetOther"
 * })
 * @ORM\HasLifecycleCallbacks
 */
abstract class Asset
{
    /**
     * @var int
     * @JMS\Type("integer")
     * @JMS\Groups({"ndr-asset"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="odr_asset_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var float
     *
     * @JMS\Groups({"ndr-asset"})
     * @JMS\Type("string")
     *
     * @ORM\Column(name="asset_value", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $value;

    /**
     * @var \DateTime
     * @JMS\Groups({"ndr-asset"})
     * @JMS\Type("DateTime")
     * @ORM\Column(name="last_edit", type="datetime", nullable=true)
     */
    private $lastEdit;

    /**
     * @var Ndr
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Ndr\Ndr", inversedBy="assets")
     * @ORM\JoinColumn(name="odr_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $ndr;

    /**
     * @var string
     * @JMS\Exclude
     */
    private $type;

    /**
     * @param string $type
     *
     * @return Asset instance
     */
    public static function factory($type)
    {
        switch ($type) {
            case 'property':
                return new AssetProperty();
            default:
                return new AssetOther();
        }
    }

    public function __clone()
    {
        $this->id = null;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set value.
     *
     * @param string $value
     *
     * @return Asset
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set lastedit.
     *
     * @param \DateTime $lastEdit
     *
     * @return Asset
     */
    public function setLastEdit($lastEdit)
    {
        $this->lastEdit = $lastEdit;

        return $this;
    }

    /**
     * Get lastedit.
     *
     * @return \DateTime
     */
    public function getLastEdit()
    {
        return $this->lastEdit;
    }

    /**
     * @return Ndr
     */
    public function getNdr()
    {
        return $this->ndr;
    }

    /**
     * @param Ndr $ndr
     *
     * @return Asset
     */
    public function setNdr(Ndr $ndr = null)
    {
        $this->ndr = $ndr;

        $ndr->setNoAssetToAdd(null);

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Asset
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateLastEdit()
    {
        $this->setLastEdit(new \DateTime());
    }
}
