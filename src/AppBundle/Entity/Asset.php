<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Asset.
 *
 * @ORM\Table(name="asset")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\AssetRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *      "property"  = "AppBundle\Entity\AssetProperty", 
 *      "other"     = "AppBundle\Entity\AssetOther"
 * })
 * @ORM\HasLifecycleCallbacks
 */
abstract class Asset
{
    /**
     * @var int
     * @JMS\Type("integer")
     * @JMS\Groups({"asset"})
     * 
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="asset_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var decimal
     * 
     * @JMS\Groups({"asset"})
     * @JMS\Type("string")
     * 
     * @ORM\Column(name="asset_value", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $value;

    /**
     * @var \DateTime
     * @JMS\Groups({"asset"})
     * @JMS\Type("DateTime")
     * @ORM\Column(name="last_edit", type="datetime", nullable=true)
     */
    private $lastedit;

    /**
     * @var int
     * @JMS\Exclude
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report", inversedBy="assets")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     */
    private $report;

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
     * @param \DateTime $lastedit
     *
     * @return Asset
     */
    public function setLastedit($lastedit)
    {
        $this->lastedit = $lastedit;

        return $this;
    }

    /**
     * Get lastedit.
     *
     * @return \DateTime
     */
    public function getLastedit()
    {
        return $this->lastedit;
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

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateLastEdit()
    {
        $this->setLastedit(new \DateTime());
    }
}
