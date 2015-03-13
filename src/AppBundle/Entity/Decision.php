<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Decisions
 *
 * @ORM\Table(name="decision")
 * @ORM\Entity
 */
class Decision
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="decision_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title",type="string", length=500)
     */
    private $title;
    
    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @var boolean
     *
     * @ORM\Column(name="client_involved_boolean", type="boolean")
     */
    private $clientInvolvedBoolean;
    
     /**
     * @ORM\Column(name="client_involved_details", type="text", nullable=true)
     */
    private $clientInvolvedDetails;
    

    /**
     * @var \DateTime
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @ORM\Column(name="decision_date", type="date", nullable=true)
     */
    private $decisionDate;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report", inversedBy="decisions")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     */
    private $report;

    
    /**
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return integer
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return integer
     */
    public function getTitle()
    {
        return $this->title;
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
     * @param boolean
     */
    public function setClientInvolvedBoolean($clientInvolvedBoolean)
    {
        $this->clientInvolvedBoolean = (boolean)$clientInvolvedBoolean;
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
     * Set lastedit
     *
     * @param \DateTime $lastedit
     * @return Decision
     */
    public function setLastedit($lastedit)
    {
        $this->lastedit = $lastedit;

        return $this;
    }

    /**
     * Set ddate
     *
     * @param \DateTime $ddate
     * @return Decision
     */
    public function setDecisionDate($ddate)
    {
        $this->decisionDate = $ddate;

        return $this;
    }

    /**
     * Get decision date
     *
     * @return \DateTime 
     */
    public function getDecisionDate()
    {
        return $this->decisionDate;
    }

    /**
     * @param Report $report
     */
    public function setReport(Report $report)
    {
        $this->report = $report;
    }
        
    /**
     * Get report
     *
     * @return Report 
     */
    public function getReport()
    {
        return $this->report;
    }
}
