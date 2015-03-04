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

}