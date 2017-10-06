<?php

namespace AppBundle\Entity\Odr;

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
 *      "property"  = "AppBundle\Entity\Odr\AssetProperty",
 *      "other"     = "AppBundle\Entity\Odr\AssetOther"
 * })
 * @ORM\HasLifecycleCallbacks
 */
abstract class Asset
{
    /**
     * @var int
     * @JMS\Type("integer")
     * @JMS\Groups({"odr-asset"})
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
     * @JMS\Groups({"odr-asset"})
     * @JMS\Type("string")
     *
     * @ORM\Column(name="asset_value", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $value;

    /**
     * @var \DateTime
     * @JMS\Groups({"odr-asset"})
     * @JMS\Type("DateTime")
     * @ORM\Column(name="last_edit", type="datetime", nullable=true)
     */
    private $lastEdit;

    /**
     * @var Odr
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Odr\Odr", inversedBy="assets")
     * @ORM\JoinColumn(name="odr_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $odr;

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
     * @return Odr
     */
    public function getOdr()
    {
        return $this->odr;
    }

    /**
     * @param Odr $odr
     *
     * @return Asset
     */
    public function setOdr(Odr $odr = null)
    {
        $this->odr = $odr;

        $odr->setNoAssetToAdd(null);

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
