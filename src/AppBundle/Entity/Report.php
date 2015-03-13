<?php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @JMS\XmlRoot("report")
 * @JMS\ExclusionPolicy("none")
 * @Assert\Callback(methods={"isValidEndDate", "isValidDateRange"})
 */
class Report
{
    const PROPERTY_AND_AFFAIRS = 2;
    /**
     * @JMS\Type("integer")
     * @var integer
     */
    private $id;
    
    /**
     * @Assert\NotBlank( message="report.startDate.notBlank")
     * @Assert\Date( message="report.startDate.invalidMessage" )
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @var \DateTime $startDate
     */
    private $startDate;
    
    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
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
     * @JMS\Exclude
     * @var string
     */
    private $period;
    
    /**
     * @JMS\Exclude
     * @var string $dueDate
     */
    private $dueDate;
    
    
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
        return new \DateTime($this->startDate->format('Y-m-d'));
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
        return new \DateTime($this->endDate->format('Y-m-d'));
    }
    
    /**
     * @return string $dueDate
     */
    public function getDueDate()
    {
        if(!empty($this->dueDate)){
            return $this->dueDate;
        }
        
        $this->dueDate = $this->endDate->modify('+8 weeks');
      
        if($this->dueDate->format("N") > 5){
            $this->dueDate->modify('next monday');
        }
        return $this->dueDate;
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
     * @return string $period
     */
    public function getPeriod()
    {
        if(!empty($this->period)){
            return $this->period;
        }
        
        if(empty($this->startDate)){
            return $this->period;
        }
        
        $startDateStr = $this->startDate->format("Y");
        $endDateStr = $this->endDate->format("Y");
        
        if($startDateStr != $endDateStr){
            $this->period = $startDateStr.' to '.$endDateStr;
            return $this->period;
        }
        $this->period = $startDateStr;
        
        return $this->period;
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