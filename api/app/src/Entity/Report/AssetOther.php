<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

#[ORM\Entity, ORM\HasLifecycleCallbacks]
class AssetOther extends Asset
{
    /**
     * type of the asset
     * Vehicles | Jewellery etc...
     * (needs refactor into an enum, as it originally was a freetext)
     */
    #[JMS\Groups(['asset'])]
    #[ORM\Column(name: 'title', type: 'string', length: 100, nullable: true)]
    private ?string $title = null;

    #[JMS\Groups(['asset'])]
    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[JMS\Type('DateTime')]
    #[JMS\Groups(['asset'])]
    #[ORM\Column(name: 'valuation_date', type: 'date', nullable: true)]
    private ?\DateTime $valuationDate = null;

    public function __construct(Report $report)
    {
        parent::__construct($report, 'other');
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setValuationDate(?\DateTime $valuationDate): static
    {
        $this->valuationDate = $valuationDate;

        return $this;
    }

    public function getValuationDate(): ?\DateTime
    {
        return $this->valuationDate;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function isEqual(Asset $asset): bool
    {
        return $asset instanceof AssetOther && $asset->getDescription() === $this->getDescription();
    }

    #[ORM\PreRemove]
    public function onPreRemove(PreRemoveEventArgs $_): void
    {
        if ($this->getReport()->getAssets()->count() === 1) {
            $this->getReport()->setNoAssetToAdd(null);
        }
    }
}
