<?php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @JMS\XmlRoot("report")
 * @Assert\GroupSequence({"Report", "Date"})
 * @Assert\Callback(methods={"isValidEndDate", "isValidDateRange"}, groups={"Date"})
 */
class Report
{
    /**
     * @JMS\Type("integer")
     * @var integer
     */
    private $id;
    
    /**
     * @JMS\Type("DateTime")
     * @Assert\NotBlank( message="report.startDate.notBlank")
     * @Assert\Date( message="report.startDate.invalidMessage" )
     * @var \DateTime $startDate
     */
    private $startDate;
    
    /**
     * @JMS\Type("DateTime")
     * @Assert\NotBlank( message="report.endDate.notBlank" )
     * @Assert\Date( message="report.endDate.invalidMessage" )
     * @var \DateTime $endDate
     */
    private $endDate;
    
    /**
     * @JMS\Type("integer")
     * @var integer $client
     */
    private $client;
    
    /**
     * @JMS\Type("integer")
     * @Assert\NotBlank( message="report.courtOrderType.notBlank" )
     * @var integer $courtOrderType
     */
    private $courtOrderType;
    
    
    /**
     * 
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param integer $id
     * @return \AppBundle\Entity\Report
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    /**
     * @return \DateTime $startDate
     */
    public function getStartDate()
    {
        return $this->startDate;
    }
    
    /**
     * @param \DateTime $startDate
     * @return \AppBundle\Entity\Report
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }
    
    /**
     * @return \DateTime $endDate
     */
    public function getEndDate()
    {
        return $this->endDate;
    }
    
    /**
     * @param \DateTime $endDate
     * @return \AppBundle\Entity\Report
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
        return $this;
    }
    
    /**
     * @return integer $client
     */
    public function getClient()
    {
        return $this->client;
    }
    
    /**
     * @param integer $client
     * @return \AppBundle\Entity\Report
     */
    public function setClient($client)
    {
        $this->client = $client;
        return $this;
    }
    
    /**
     * @return integer $courtOrderType
     */
    public function getCourtOrderType()
    {
        return $this->courtOrderType;
    }
    
    /**
     * @param integer $courtOrderType
     * @return \AppBundle\Entity\Report
     */
    public function setCourtOrderType($courtOrderType)
    {
        $this->courtOrderType = $courtOrderType;
        return $this;
    }
    
    /**
     * @param ExecutionContextInterface $context
     */
    public function isValidEndDate(ExecutionContextInterface $context)
    {
        if($this->startDate > $this->endDate){
            $context->addViolationAt('endDate', 'report.endDate.beforeStart');
        }
    }
    
    /**
     * @param ExecutionContextInterface $context
     * @return type
     */
    public function isValidDateRange(ExecutionContextInterface $context)
    {
        if(!empty($this->endDate)){
            $dateInterval = $this->startDate->diff($this->endDate);
        }else{
            $context->addViolationAt('endDate','report.endDate.invalidMessage');
            return null;
        }
        
        if($dateInterval->days > 366){
            $context->addViolationAt('endDate','report.endDate.greaterThan12Months');
        }
    }
}