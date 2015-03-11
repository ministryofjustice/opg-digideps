<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\ExecutionContextInterface;
/**
 * @JMS\XmlRoot("decision")
 * @Assert\Callback(methods={"isValidDateRange"})
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
     * @Assert\NotBlank( message="decision.title.notBlank" )
     * @Assert\Length( min=2, minMessage="decision.title.length")
     * @JMS\Type("string")
     * @var string
     */
    private $title;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank( message="decision.description.notBlank" )
     * @Assert\Length( min=2, minMessage="decision.description.length")
     * @var string
     */
    private $description;

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @Assert\NotBlank( message="decision.decisionDate.notBlank")
     * @Assert\Date( message="decision.decisionDate.invalidMessage" )
     * @var \DateTime
     */
    private $decisionDate;

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
        
    public function getTitle()
    {
        return $this->title;
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

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setDecisionDate(\DateTime $date = null)
    {
        $this->decisionDate = $date;
    }

    public function getDecisionDate()
    {
        return $this->decisionDate;
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
    
    /**
     * @param ExecutionContextInterface $context
     * @return boolean
     */
    public function isValidDateRange(ExecutionContextInterface $context)
    {
        if (empty($this->decisionDate)) {
            return; // the notEmpty validator will take care of that
        }
        
        $reportStartDate = clone $this->report->getStartDate();
        $reportEndDate = clone $this->report->getEndDate();
        
        $reportStartDate->setTime(0,0,0);
        $reportEndDate->setTime(23, 59, 59);
        
        if ($this->decisionDate->getTimestamp() > $reportEndDate->getTimestamp() ||
            $this->decisionDate->getTimestamp() < $reportStartDate->getTimestamp()
        ) {
            $context->addViolationAt('decisionDate','decision.decisionDate.notInReportRange', [
                '{{from}}' => $reportStartDate->format('d/m/Y'),
                '{{to}}' => $reportEndDate->format('d/m/Y'),
            ]);
        }
    }

}