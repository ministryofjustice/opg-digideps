<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use OPG\Digideps\Backend\Entity\Traits\CreateUpdateTimestamps;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Repository\AssetRepository;

#[ORM\Table(name: 'asset')]
#[ORM\Entity(repositoryClass: AssetRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(['property' => AssetProperty::class, 'other' => AssetOther::class])]
#[ORM\HasLifecycleCallbacks]
abstract class Asset
{
    use CreateUpdateTimestamps;

    #[JMS\Type('integer')]
    #[JMS\Groups(['asset'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'asset_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[JMS\Groups(['asset'])]
    #[JMS\Type('string')]
    #[ORM\Column(name: 'asset_value', type: 'decimal', precision: 14, scale: 2, nullable: true)]
    private ?string $value = null;

    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Report::class, inversedBy: 'assets')]
    private Report $report;

    final public function __construct(Report $report)
    {
        $this->report = $report;
    }

    public static function factory(string $type, Report $report): Asset
    {
        return match ($type) {
            'property' => new AssetProperty($report),
            default => new AssetOther($report),
        };
    }

    public function __clone()
    {
        $this->id = null;
    }

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function setId(int $id): static
    {
        if ($this->id === null) {
            $this->id = $id;
        } elseif ($id === 0) {
            throw new \DomainException('You may not set the id of an entity to zero.');
        } else {
            throw new \LogicException('You may not set the id of an entity more than once.');
        }

        return $this;
    }

    #[JMS\VirtualProperty]
    #[JMS\SerializedName('type')]
    #[JMS\Groups(['asset'])]
    public function getAssetType(): string
    {
        return $this->getType();
    }

    public function setValue(null|float|int|string $value): static
    {
        $this->value = $value !== null ? (string)$value : null;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    #[JMS\VirtualProperty]
    #[JMS\Type('float')]
    #[JMS\SerializedName('value_total')]
    #[JMS\Groups(['asset'])]
    public function getValueTotal(): ?float
    {
        return $this->value === null ? null : (float)$this->value;
    }

    /**
     * Set report and set to false the report.noAssetToAdd status.
     */
    public function setReport(Report $report): static
    {
        $this->report = $report;

        // reset choice
        $report->setNoAssetToAdd(null);

        return $this;
    }

    public function getReport(): Report
    {
        return $this->report;
    }

    abstract public function isEqual(Asset $asset): bool;
    abstract protected function getType(): string;
}
