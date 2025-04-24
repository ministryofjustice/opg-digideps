<?php

namespace App\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="mental_capacity")
 *
 * @ORM\Entity
 */
class MentalCapacity
{
    public const CAPACITY_CHANGED = 'changed';
    public const CAPACITY_STAYED_SAME = 'stayedSame';

    /**
     * @var int
     *
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="mental_capacity_id_seq", allocationSize=1, initialValue=1)
     */
    #[JMS\Type('integer')]
    private $id;

    /**
     * @var Report
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Report\Report", inversedBy="mentalCapacity")
     *
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $report;

    /**
     * @var string changed | stayedSame (see constants)
     *
     *
     *
     * @ORM\Column(name="has_capacity_changed", type="string", length=25, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['mental-capacity'])]
    private $hasCapacityChanged;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="has_capacity_changed_details", type="text", nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['mental-capacity'])]
    private $hasCapacityChangedDetails;

    /**
     * @var \Date
     *
     *
     *
     * @ORM\Column(name="mental_assessment_date", type="date", nullable=true)
     */
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['mental-capacity'])]
    private $mentalAssessmentDate;

    public function __construct(Report $report)
    {
        $this->mentalAssessmentDate = null;
        $this->report = $report;
        $report->setMentalCapacity($this);
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
     * Set report.
     *
     * @param Report $report
     *
     * @return Contact
     */
    public function setReport(Report $report = null)
    {
        $this->report = $report;

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

    public function getHasCapacityChanged()
    {
        return $this->hasCapacityChanged;
    }

    public function getHasCapacityChangedDetails()
    {
        return $this->hasCapacityChangedDetails;
    }

    public function setHasCapacityChanged($hasCapacityChanged)
    {
        $this->hasCapacityChanged = $hasCapacityChanged;

        return $this;
    }

    public function setHasCapacityChangedDetails($hasCapacityChangedDetails)
    {
        $this->hasCapacityChangedDetails = $hasCapacityChangedDetails;

        return $this;
    }

    public function getMentalAssessmentDate()
    {
        return $this->mentalAssessmentDate;
    }

    public function setMentalAssessmentDate($mentalAssessmentDate)
    {
        $this->mentalAssessmentDate = $mentalAssessmentDate;

        return $this;
    }

    public function cleanUpUnusedData()
    {
        if (self::CAPACITY_STAYED_SAME == $this->hasCapacityChanged) {
            $this->hasCapacityChangedDetails = null;
        }
    }
}
