<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use OPG\Digideps\Backend\Entity\Report\Asset;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait AssetTrait
{
    /**
     * @var Collection<int, Asset>
     */
    #[JMS\Groups(['asset'])]
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\Asset>')]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: Asset::class, cascade: ['persist', 'remove'])]
    private Collection $assets;

    /**
     * deputy declaration saying there are no assets. Required (true/false) if no assets are added
     */
    #[JMS\Type('boolean')]
    #[JMS\Groups(['report'])]
    #[ORM\Column(name: 'no_asset_to_add', type: 'boolean', nullable: true, options: ['default' => false])]
    private ?bool $noAssetToAdd;

    public function addAsset(Asset $assets): static
    {
        $this->assets[] = $assets;

        return $this;
    }

    public function removeAsset(Asset $assets): void
    {
        $this->assets->removeElement($assets);
    }

    /**
     * @return Collection<int, Asset>
     */
    public function getAssets(): Collection
    {
        return $this->assets;
    }

    #[JMS\VirtualProperty]
    #[JMS\Type('double')]
    #[JMS\SerializedName('assets_total_value')]
    #[JMS\Groups(['asset'])]
    public function getAssetsTotalValue(): float
    {
        $ret = 0.0;
        foreach ($this->getAssets() as $asset) {
            $ret += $asset->getValueTotal() ?? 0.0;
        }

        return $ret;
    }

    public function setNoAssetToAdd(?bool $noAssetToAdd): static
    {
        $this->noAssetToAdd = $noAssetToAdd;

        return $this;
    }

    public function getNoAssetToAdd(): ?bool
    {
        return $this->noAssetToAdd;
    }
}
