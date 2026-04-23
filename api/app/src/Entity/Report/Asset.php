<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use OPG\Digideps\Backend\Entity\Traits\CreateUpdateTimestamps;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Repository\AssetRepository;
use OPG\Digideps\Backend\Entity\Report\AssetProperty;

#[ORM\Table(name: 'asset')]
#[ORM\Entity(repositoryClass: AssetRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(['property' => AssetProperty::class, 'other' => 'App\Entity\Report\AssetOther'])]
#[ORM\HasLifecycleCallbacks]
abstract class Asset
{
    use CreateUpdateTimestamps;

    /**
     * @var int
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['asset'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'asset_id_seq', allocationSize: 1, initialValue: 1)]
    private $id;

    /**
     * @var float
     */
    #[JMS\Groups(['asset'])]
    #[JMS\Type('string')]
    #[ORM\Column(name: 'asset_value', type: 'decimal', precision: 14, scale: 2, nullable: true)]
    private $value;

    /**
     * @var Report
     */
    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Report::class, inversedBy: 'assets')]
    private $report;

    /**
     * Discriminator field.
     *
     * @var string
     */
    #[JMS\Exclude]
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
     */
    public function setValue($value): static
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
     * @return float|null
     */
    #[JMS\VirtualProperty]
    #[JMS\Type('float')]
    #[JMS\SerializedName('value_total')]
    #[JMS\Groups(['asset'])]
    public function getValueTotal()
    {
        return $this->value;
    }

    /**
     * Set report and set to false the report.noAssetToAdd status.
     */
    public function setReport(?Report $report = null): static
    {
        $this->report = $report;

        // reset choice
        $report->setNoAssetToAdd(null);

        return $this;
    }

    /**
     * Get report.
     *
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }
}
