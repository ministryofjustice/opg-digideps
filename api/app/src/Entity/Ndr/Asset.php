<?php

namespace App\Entity\Ndr;

use App\Entity\Traits\CreateUpdateTimestamps;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Asset.
 *
 * @ORM\Table(name="odr_asset")
 *
 * @ORM\Entity(repositoryClass="App\Repository\NdrAssetRepository")
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 *
 * @ORM\DiscriminatorColumn(name="type", type="string")
 *
 * @ORM\DiscriminatorMap({
 *      "property"  = "App\Entity\Ndr\AssetProperty",
 *      "other"     = "App\Entity\Ndr\AssetOther"
 * })
 *
 * @ORM\HasLifecycleCallbacks
 */
abstract class Asset
{
    use CreateUpdateTimestamps;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     *
     * @JMS\Groups({"ndr-asset"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\SequenceGenerator(sequenceName="odr_asset_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var float
     *
     * @JMS\Groups({"ndr-asset"})
     *
     * @JMS\Type("string")
     *
     * @ORM\Column(name="asset_value", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $value;

    /**
     * @var Ndr
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Ndr\Ndr", inversedBy="assets")
     *
     * @ORM\JoinColumn(name="odr_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $ndr;

    /**
     * @var string
     *
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
     * @return Ndr
     */
    public function getNdr()
    {
        return $this->ndr;
    }

    /**
     * @return Asset
     */
    public function setNdr(?Ndr $ndr = null)
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
}
