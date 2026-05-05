<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use OPG\Digideps\Backend\Entity\AssetInterface;
use OPG\Digideps\Backend\Entity\Report\Asset;
use OPG\Digideps\Backend\Entity\Report\Report;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait AssetTrait
{
    /**
     * @var AssetInterface[]
     */
    #[JMS\Groups(['asset'])]
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\Asset>')]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: Asset::class, cascade: ['persist', 'remove'])]
    private $assets;

    /**
     * @var bool deputy declaration saying there are no assets. Required (true/false) if no assets are added
     */
    #[JMS\Type('boolean')]
    #[JMS\Groups(['report'])]
    #[ORM\Column(name: 'no_asset_to_add', type: 'boolean', options: ['default' => false], nullable: true)]
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
     * @return Collection<int, AssetInterface>|AssetInterface[]
     */
    public function getAssets(): Collection|array
    {
        return $this->assets;
    }

    /**
     * Get assets total value.
     *
     * @return float
     */
    #[JMS\VirtualProperty]
    #[JMS\Type('double')]
    #[JMS\SerializedName('assets_total_value')]
    #[JMS\Groups(['asset'])]
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
