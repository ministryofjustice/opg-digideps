<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\ExecutionContextInterface;
/**
 * @JMS\XmlRoot("decision")
 */
class Decision
{
    /**
     * @JMS\Type("integer")
     * @var integer
     */
    private $id;

    /**
     * @JMS\Type("integer")
     * @var integer
     */
    private $reportId;
    
    /**
     * Only used to hold the Report object, needed by the validators for date range reasons
     * @JMS\Exclude
     * @var Report
     */
    private $report;
    
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank( message="decision.description.notBlank" )
     * @Assert\Length( min=2, minMessage="decision.description.length")
     * @var string
     */
    private $description;

    /**
     * @Assert\NotBlank( message="decision.clientInvolvedBoolean.notBlank")
     * @JMS\Type("boolean")
     * @var boolean
     */
    private $clientInvolvedBoolean;

    /**
     * @Assert\NotBlank( message="decision.clientInvolvedDetails.notBlank")
     * @Assert\Length( min=2, minMessage="decision.clientInvolvedDetails.length")
     * @JMS\Type("string")
     * @var boolean
     */
    private $clientInvolvedDetails;


    public function getId()
    {
        return $this->id;
    }

    public function setReportId($reportId)
    {
        $this->reportId = $reportId;
    }

    public function getReportId()
    {
        return $this->reportId;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setClientInvolvedBoolean($clientInvolvedBoolean)
    {
        $this->clientInvolvedBoolean = $clientInvolvedBoolean;
    }

    public function getClientInvolvedBoolean()
    {
        return $this->clientInvolvedBoolean;
    }

    public function setClientInvolvedDetails($clientInvolvedDetails)
    {
        $this->clientInvolvedDetails = $clientInvolvedDetails;
    }

    public function getClientInvolvedDetails()
    {
        return $this->clientInvolvedDetails;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }
    
    /**
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param Report $report
     */
    public function setReport(Report $report)
    {
        $this->report = $report;
    }
    
}