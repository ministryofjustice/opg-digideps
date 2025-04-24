<?php

namespace App\Entity\Report;

use App\Entity\Traits\CreateUpdateTimestamps;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Asset.
 *
 * @ORM\Table(name="asset")
 *
 * @ORM\Entity(repositoryClass="App\Repository\AssetRepository")
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 *
 * @ORM\DiscriminatorColumn(name="type", type="string")
 *
 * @ORM\DiscriminatorMap({
 *      "property"  = "App\Entity\Report\AssetProperty",
 *      "other"     = "App\Entity\Report\AssetOther"
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
     *
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\SequenceGenerator(sequenceName="asset_id_seq", allocationSize=1, initialValue=1)
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['asset'])]
    private $id;

    /**
     * @var float
     *
     *
     *
     * @ORM\Column(name="asset_value", type="decimal", precision=14, scale=2, nullable=true)
     */
    #[JMS\Groups(['asset'])]
    #[JMS\Type('string')]
    private $value;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Report\Report", inversedBy="assets")
     *
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
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
     *
     *
     *
     *
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
     *
     * @param Report $report
     *
     * @return Asset
     */
    public function setReport(Report $report = null)
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
