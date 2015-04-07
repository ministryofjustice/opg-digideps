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
     * @JMS\Type("array")
     * @var array $accounts
     */
    private $accounts;
    
    /**
     * @JMS\Type("array<AppBundle\Entity\Account>")
     * @JMS\Accessor(getter="getAccounts", setter="setAccounts")
     * @var array $accountObs
     */
    private $accountObjs;
    
    /**
     * @JMS\Type("array")
     * @var array $contacts
     */
    private $contacts;
    
    /**
     * @JMS\Type("array")
     * @var array $assets
     */
    private $assets;
    
    /**
     * @JMS\Type("array")
     * @var array $decisions
     */
    private $decisions;
    
    /**
     * @JMS\Exclude
     * @var array
     */
    private $outstandingAccounts;
    
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
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }
    
    /**
     * @param \DateTime $startDate
     * 
     * @return \AppBundle\Entity\Report
     */
    public function setStartDate(\DateTime $startDate = null)
    {
        if ($startDate instanceof \DateTime) {
            $startDate->setTime(0, 0, 0);
        }
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
     * Return the date 8 weeks after the end date
     * 
     * @return string $dueDate
     */
    public function getDueDate()
    {
        $dueDate = clone $this->endDate;
        $dueDate->modify('+8 weeks');
        
        return $dueDate;
    }
    
    /**
     * @param \DateTime $endDate
     * @return \AppBundle\Entity\Report
     */
    public function setEndDate(\DateTime $endDate = null)
    {
        if ($endDate instanceof \DateTime) {
            $endDate->setTime(23, 59, 59);
        }
        $this->endDate = $endDate;
        
        return $this;
    }
    
    /**
     * Return string representation of the start-end date period
     * e.g. 2004 to 2005
     * 
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
     * @return array $accounts
     */
    public function getAccounts()
    {
        return $this->accounts;
    }
    
    /**
     * @param array $accounts
     * @return \AppBundle\Entity\Report
     */
    public function setAccounts($accounts)
    {
        $this->accounts = $accounts;
        return $this;
    }
    
    /**
     * @return boolean
     */
    public function hasOutstandingAccounts()
    {
        if(empty($this->accounts)){
            return false;
        }
       
        foreach($this->accounts as $account){
            if(!$account->hasClosingBalance()){
                return true;
            }
        }
        return false;
    }
    
    /**
     * 
     * @return array $outstandingAccounts
     */
    public function getOutstandingAccounts()
    {  
        if($this->hasOutstandingAccounts() && empty($this->outstandingAccounts)){
            foreach ($this->accounts as $account){
                if(!$account->hasClosingBalance()){
                    $this->outstandingAccounts[] = $account;
                }
            }
        }
        return $this->outstandingAccounts;
    }
    
    /**
     * 
     * @return array $contacts
     */
    public function getContacts()
    {
        return $this->contacts;
    }
    
    /**
     * @param array $contacts
     * @return array $contacts
     */
    public function setContacts($contacts)
    {
        $this->contacts = $contacts;
        return $this->contacts;
    }
    
    /**
     * 
     * @return boolean
     * @return boolean@var boolean
     */
    public function hasContacts()
    {
        if(empty($this->contacts)){
            return false;
        }
        return true;
    }
    
    /**
     * @var array $decisions
     */
    public function getDecisions()
    {
        return $this->decisions;
    }
    
    /**
     * 
     * @param type $decisions
     * @return \AppBundle\Entity\Report
     */
    public function setDecisions($decisions)
    {
        $this->decisions = $decisions;
        return $this;
    }
    
    /**
     * 
     * @return boolean
     */
    public function hasDecisions()
    {
        if(empty($this->decisions)){
            return false;
        }
        return true;
    }
    
    /**
     * 
     * @param array $assets
     * @return \AppBundle\Entity\Report
     */
    public function setAssets($assets)
    {
        $this->assets = $assets;
        return $this;
    }
    
    /**
     * @return array $assets
     */
    public function getAssets()
    {
        return $this->assets;
    }
    
    /**
     * 
     * @return boolean
     */
    public function hasAssets()
    {
        if(empty($this->assets)){
            return false;
        }
        return true;
    }
    
    /**
     * 
     * @return boolean
     */
    public function readyToSubmit()
    {
        if($this->courtOrderType == self::PROPERTY_AND_AFFAIRS){
            if($this->hasOutstandingAccounts() || !$this->hasContacts() || !$this->hasAssets() || !$this->hasDecisions()){
                return false;
            }
        }else{
            if(!$this->hasContacts() || !$this->hasDecisions()){
                return false;
            }
        }
        return true;
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
    
    /**
     * Return true when the report is Due (today's date => report end date)
     * @return boolean
     */
    public function isDue()
    {
        if (!$this->getEndDate() instanceof \DateTime) {
            return false;
        }
        
        // reset time on dates
        $today = new \DateTime;
        $today->setTime(0, 0, 0);
        
        $reportDueOn = clone $this->getEndDate();
        $reportDueOn->setTime(0, 0, 0);
        
        return $today >= $reportDueOn;
    }
}