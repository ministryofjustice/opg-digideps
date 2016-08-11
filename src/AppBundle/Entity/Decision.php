<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Decisions.
 *
 * @ORM\Table(name="decision")
 * @ORM\Entity
 */
class Decision
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="decision_id_seq", allocationSize=1, initialValue=1)
     * @JMS\Groups({"decision", "related"})
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * @var string
     * @JMS\Groups({"decision", "related"})
     * @JMS\Type("string")
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @var bool
     * @JMS\Groups({"decision", "related"})
     * @JMS\Type("boolean")
     * @ORM\Column(name="client_involved_boolean", type="boolean")
     */
    private $clientInvolvedBoolean;

    /**
     * @JMS\Groups({"decision", "related"})
     * @JMS\Type("string")
     * @ORM\Column(name="client_involved_details", type="text", nullable=true)
     */
    private $clientInvolvedDetails;

    /**
     * @var int
     * @JMS\Groups({"related"})
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report", inversedBy="decisions")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     */
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
        $this->clientInvolvedBoolean = (boolean) $clientInvolvedBoolean;
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

    /**
     * Set lastedit.
     *
     * @param \DateTime $lastedit
     *
     * @return Decision
     */
    public function setLastedit($lastedit)
    {
        $this->lastedit = $lastedit;

        return $this;
    }

    /**
     * @param Report $report
     */
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
