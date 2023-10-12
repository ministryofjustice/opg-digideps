<?php

namespace App\Entity\Report\Traits;

use App\Entity\AssetInterface;
use App\Entity\Report\Asset;
use App\Entity\Report\Report;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait AssetTrait
{
    /**
     * @var AssetInterface[]
     *
     * @JMS\Groups({"asset"})
     *
     * @JMS\Type("ArrayCollection<App\Entity\Report\Asset>")
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Report\Asset", mappedBy="report", cascade={"persist", "remove"})
     */
    private $assets;

    /**
     * @var bool deputy declaration saying there are no assets. Required (true/false) if no assets are added
     *
     * @JMS\Type("boolean")
     *
     * @JMS\Groups({"report"})
     *
     * @ORM\Column(name="no_asset_to_add", type="boolean", options={ "default": false}, nullable=true)
     */
    private $noAssetToAdd;

    /**
     * Add assets.
     *
     * @return Report
     */
    public function addAsset(Asset $assets)
    {
        $this->assets[] = $assets;

        return $this;
    }

    /**
     * Remove assets.
     */
    public function removeAsset(Asset $assets)
    {
        $this->assets->removeElement($assets);
    }

    /**
     * Get assets.
     *
     * @return AssetInterface[]
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * Get assets total value.
     *
     * @JMS\VirtualProperty
     *
     * @JMS\Type("double")
     *
     * @JMS\SerializedName("assets_total_value")
     *
     * @JMS\Groups({"asset"})
     *
     * @return float
     */
    public function getAssetsTotalValue()
    {
        $ret = 0;
        foreach ($this->getAssets() as $asset) {
            $ret += $asset->getValueTotal();
        }

        return $ret;
    }

    /**
     * Set noAssetToAdd.
     *
     * @param bool $noAssetToAdd
     *
     * @return Report
     */
    public function setNoAssetToAdd($noAssetToAdd)
    {
        $this->noAssetToAdd = $noAssetToAdd;

        return $this;
    }

    /**
     * Get noAssetToAdd.
     *
     * @return bool
     */
    public function getNoAssetToAdd()
    {
        return $this->noAssetToAdd;
    }
}
