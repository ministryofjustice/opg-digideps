<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * CourtOrderType.
 *
 * @JMS\XmlRoot("court_order_type")
 * @ORM\Table(name="court_order_type")
 * @ORM\Entity
 */
class CourtOrderType
{
    /**
     * @var int
     * @JMS\Type("integer")
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="court_order_type_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     * @JMS\Type("string")
     * @ORM\Column(name="name", type="string", length=100, nullable=true)
     */
    private $name;

    /**
     * @JMS\Exclude
     * @JMS\Accessor(getter="getReportIds")
     * @JMS\Type("array")
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report", mappedBy="courtOrderType")
     */
    private $reports;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->reports = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name.
     *
     * @param string $name
     *
     * @return CourtOrderType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add reports.
     *
     * @param Report $reports
     *
     * @return CourtOrderType
     */
    public function addReport(Report $reports)
    {
        $this->reports[] = $reports;

        return $this;
    }

    /**
     * Remove reports.
     *
     * @param Report $reports
     */
    public function removeReport(Report $reports)
    {
        $this->reports->removeElement($reports);
    }

    public function getReportIds()
    {
        $reports = [];

        if (!empty($this->reports)) {
            foreach ($this->reports as $report) {
                $reports[] = $report->getId();
            }
        }

        return $reports;
    }

    /**
     * Get reports.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReports()
    {
        return $this->reports;
    }
}
