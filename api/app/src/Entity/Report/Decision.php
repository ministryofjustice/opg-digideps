<?php

namespace App\Entity\Report;

use App\Entity\Traits\CreateUpdateTimestamps;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Decisions.
 */
#[ORM\Table(name: 'decision')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Decision
{
    use CreateUpdateTimestamps;

    /**
     * @var int
     *
     *
     *
     *
     *
     * @JMS\Groups({"decision"})
     *
     * @JMS\Type("integer")
     */
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'decision_id_seq', allocationSize: 1, initialValue: 1)]
    private $id;

    /**
     * @var string
     *
     * @JMS\Groups({"decision"})
     *
     * @JMS\Type("string")
     */
    #[ORM\Column(type: 'text')]
    private $description;

    /**
     * @var bool
     *
     * @JMS\Groups({"decision"})
     *
     * @JMS\Type("boolean")
     */
    #[ORM\Column(name: 'client_involved_boolean', type: 'boolean')]
    private $clientInvolvedBoolean;

    /**
     * @var string
     *
     * @JMS\Groups({"decision"})
     *
     * @JMS\Type("string")
     */
    #[ORM\Column(name: 'client_involved_details', type: 'text', nullable: true)]
    private $clientInvolvedDetails;

    /**
     * @var Report
     */
    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Report::class, inversedBy: 'decisions')]
    private $report;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @param bool
     */
    public function setClientInvolvedBoolean($clientInvolvedBoolean)
    {
        $this->clientInvolvedBoolean = (bool) $clientInvolvedBoolean;
    }

    /*
     * @return boolean
     */
    public function getClientInvolvedBoolean()
    {
        return $this->clientInvolvedBoolean;
    }

    /**
     * @param $clientInvolvedDetails string
     */
    public function setClientInvolvedDetails($clientInvolvedDetails)
    {
        $this->clientInvolvedDetails = $clientInvolvedDetails;
    }

    /**
     * @return string
     */
    public function getClientInvolvedDetails()
    {
        return $this->clientInvolvedDetails;
    }

    public function setReport(Report $report)
    {
        $this->report = $report;
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
}
