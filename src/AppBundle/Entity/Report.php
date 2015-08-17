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
     * @JMS\Groups({"startEndDates"})
     * @var \DateTime $startDate
     */
    private $startDate;
    
    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"startEndDates"})
     * @Assert\NotBlank( message="report.endDate.notBlank" )
     * @Assert\Date( message="report.endDate.invalidMessage" )
     * @var \DateTime $endDate
     */
    private $endDate;

    /**
     * @var \DateTime $submitDate
     * @JMS\Accessor(getter="getSubmitDate", setter="setSubmitDate")
     * @JMS\Type("DateTime")
     * @JMS\Groups({"submit"})
     */
    private $submitDate;
    
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
     * @var Account[]
     */
    private $accounts;
    
    /**
     * This is not used. For consistency, it should hold the account objects, and $accounts should hold integers
     * 
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
     * @JMS\Type("string")
     * @var string $reasonForNoContacts
     */
    private $reasonForNoContacts;
    
    /**
     * @JMS\Type("string")
     * @var string $reasonForNoDecisions
     */
    private $reasonForNoDecisions;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="safeguarding.doYouLiveWithClient.notBlank", groups={"safeguarding"})
     */
    private $doYouLiveWithClient;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="safeguarding.howOftenDoYouVisit.notBlank", groups={"safeguarding-no"} )
     */
    private $howOftenDoYouVisit;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="safeguarding.howOftenDoYouPhoneOrVideoCall.notBlank", groups={"safeguarding-no"})
     */
    private $howOftenDoYouPhoneOrVideoCall;
    
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="safeguarding.howOftenDoYouWriteEmailOrLetter.notBlank", groups={"safeguarding-no"})
     */
    private $howOftenDoYouWriteEmailOrLetter;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="safeguarding.howOftenDoesClientSeeOtherPeople.notBlank", groups={"safeguarding-no"})
     */
    private $howOftenDoesClientSeeOtherPeople;

    /**
     * @JMS\Type("string")
     */
    private $anythingElseToTell;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="safeguarding.doesClientReceivePaidCare.notBlank", groups={"safeguarding"})
     */
    private $doesClientReceivePaidCare;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="safeguarding.howIsCareFunded.notBlank", groups={"safeguarding-paidCare"})
     */
    private $howIsCareFunded;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="safeguarding.whoIsDoingTheCaring.notBlank", groups={"safeguarding"})
     */
    private $whoIsDoingTheCaring;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="safeguarding.doesClientHaveACarePlan.notBlank", groups={"safeguarding"})
     */
    private $doesClientHaveACarePlan;

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @Assert\NotBlank(message="safeguarding.whenWasCarePlanLastReviewed.notBlank", groups={"safeguarding-hasCarePlan"})
     * @Assert\Date( message="safeguarding.whenWasCarePlanLastReviewed.invalidMessage", groups={"safeguarding-hasCarePlan"} )
     */
    private $whenWasCarePlanLastReviewed;
    
    /**
     * @JMS\Type("boolean")
     * @var boolean $noAssetToAdd
     */
    private $noAssetToAdd;
    
    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"submit"})
     * @Assert\True(message="report.submissionExceptions.submitted", groups={"submitted"})
     * @Assert\False(message="report.submissionExceptions.notSubmitted", groups={"notSubmitted"})
     * 
     * @var boolean
     */
    private $submitted;
    
    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"reviewed"})
     * @Assert\True(message="report.submissionExceptions.reviewedAndChecked", groups={"reviewedAndChecked"})
     * 
     * @var boolean
     */
    private $reviewed;
    
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"furtherInformation"})
     * @var string
     */
    private $furtherInformation;
    
    /**
     * @JMS\Type("boolean")
     * @var boolean
     */
    private $reportSeen;
    
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
     * Get submitDate
     *
     * @return \DateTime
     */
    public function getSubmitDate()
    {
        if($this->submitted){
            $submitDate = $this->submitDate;
        }
        else{
            $submitDate = null;
        }
        return $submitDate;
    }

    /**
     * @param \DateTime $submitDate
     * @return \AppBundle\Entity\Report
     */
    public function setSubmitDate(\DateTime $submitDate = null)
    {
        $this->submitDate = $submitDate;

        return $this;
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
     * @return array of integers (Account IDs)
     */
    public function getAccountIds()
    {
        return array_map(function($account) {
            return $account->getId();
        }, $this->accounts);
    }
    
    /**
     * @param array $accounts
     * @return \AppBundle\Entity\Report
     */
    public function setAccounts($accounts)
    {
        foreach ($accounts as $account) {
            $account->setReportObject($this);
        }
        
        $this->accounts = $accounts;
        return $this;
    }
    
    public function missingAccounts()
    {
        if( $this->courtOrderType != self::PROPERTY_AND_AFFAIRS ){
            return false;
        }
        
        if(empty($this->accounts)){
            return true;
        }
        return false;
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
    public function missingContacts()
    {
        if(empty($this->contacts) && empty($this->reasonForNoContacts)){
            return true;
        }
        return false;
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
    public function missingDecisions()
    {
        if(empty($this->decisions) && empty($this->reasonForNoDecisions)){
            return true;
        }
        return false;
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
    public function missingAssets()
    {
        if( $this->courtOrderType != self::PROPERTY_AND_AFFAIRS ){
            return false;
        }
        
        if(empty($this->assets) && (!$this->noAssetToAdd)){
            return true;
        }
        return false;
    }
    
    /**
     * @return boolean
     * @Assert\True(message="report.submissionExceptions.readyForSubmission", groups={"readyforSubmission"})
     */
    public function isReadyToSubmit()
    {
        if($this->courtOrderType == self::PROPERTY_AND_AFFAIRS){
            if($this->hasOutstandingAccounts() || $this->missingAccounts() || $this->missingContacts() || $this->missingAssets() || $this->missingDecisions() || $this->missingSafeguardingInfo()){
                return false;
            }
        }else{
            if($this->missingContacts() || $this->missingDecisions() || $this->missingSafeguardingInfo()){
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
     * @Assert\True(message="report.submissionExceptions.due", groups={"due"})
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
    
    /**
     * @param string $reasonForNoContacts
     * @return \AppBundle\Entity\Report
     */
    public function setReasonForNoContacts($reasonForNoContacts)
    {
        $this->reasonForNoContacts = $reasonForNoContacts;
        return $this;
    }
    
    /**
     * @return string $reasonForNoContacts
     */
    public function getReasonForNoContacts()
    {
        return $this->reasonForNoContacts;
    }
    
    /**
     * @param string $reasonForNoDecisions
     * @return \AppBundle\Entity\Report
     */
    public function setReasonForNoDecisions($reasonForNoDecisions)
    {
        $this->reasonForNoDecisions = $reasonForNoDecisions;
        return $this;
    }
    
    /**
     * @return string $reasonForNoDecisions
     */
    public function getReasonForNoDecisions()
    {
        return $this->reasonForNoDecisions;
    }
    
    /**
     * Set doYouLiveWithClient
     *
     * @param string $doYouLiveWithClient
     * @return Report
     */
    public function setDoYouLiveWithClient($doYouLiveWithClient)
    {
        $this->doYouLiveWithClient = $doYouLiveWithClient;

        return $this;
    }

    /**
     * Get doYouLiveWithClient
     *
     * @return string 
     */
    public function getDoYouLiveWithClient()
    {
        return $this->doYouLiveWithClient;
    }

    /**
     * Set howOftenDoYouVisit
     *
     * @param string $howOftenDoYouVisit
     * @return Report
     */
    public function setHowOftenDoYouVisit($howOftenDoYouVisit)
    {
        $this->howOftenDoYouVisit = $howOftenDoYouVisit;

        return $this;
    }

    /**
     * Get howOftenDoYouVisit
     *
     * @return string 
     */
    public function getHowOftenDoYouVisit()
    {
        return $this->howOftenDoYouVisit;
    }

    /**
     * Set howOftenDoYouPhoneOrVideoCall
     *
     * @param string $howOftenDoYouPhoneOrVideoCall
     * @return Report
     */
    public function setHowOftenDoYouPhoneOrVideoCall($howOftenDoYouPhoneOrVideoCall)
    {
        $this->howOftenDoYouPhoneOrVideoCall = $howOftenDoYouPhoneOrVideoCall;

        return $this;
    }

    /**
     * Get howOftenDoYouPhoneOrVideoCall
     *
     * @return string 
     */
    public function getHowOftenDoYouPhoneOrVideoCall()
    {
        return $this->howOftenDoYouPhoneOrVideoCall;
    }

    /**
     * Set howOftenDoYouWriteEmailOrLetter
     *
     * @param string $howOftenDoYouWriteEmailOrLetter
     * @return Report
     */
    public function setHowOftenDoYouWriteEmailOrLetter($howOftenDoYouWriteEmailOrLetter)
    {
        $this->howOftenDoYouWriteEmailOrLetter = $howOftenDoYouWriteEmailOrLetter;

        return $this;
    }

    /**
     * Get howOftenDoYouWriteEmailOrLetter
     *
     * @return string 
     */
    public function getHowOftenDoYouWriteEmailOrLetter()
    {
        return $this->howOftenDoYouWriteEmailOrLetter;
    }

    /**
     * Set howOftenDoesClientSeeOtherPeople
     *
     * @param string $howOftenDoesClientSeeOtherPeople
     * @return Report
     */
    public function setHowOftenDoesClientSeeOtherPeople($howOftenDoesClientSeeOtherPeople)
    {
        $this->howOftenDoesClientSeeOtherPeople = $howOftenDoesClientSeeOtherPeople;

        return $this;
    }

    /**
     * Get howOftenDoesClientSeeOtherPeople
     *
     * @return string 
     */
    public function getHowOftenDoesClientSeeOtherPeople()
    {
        return $this->howOftenDoesClientSeeOtherPeople;
    }

    /**
     * Set anythingElseToTell
     *
     * @param string $anythingElseToTell
     * @return Report
     */
    public function setAnythingElseToTell($anythingElseToTell)
    {
        $this->anythingElseToTell = $anythingElseToTell;

        return $this;
    }

    /**
     * Get anythingElseToTell
     *
     * @return string 
     */
    public function getAnythingElseToTell()
    {
        return $this->anythingElseToTell;
    }

    /**
     * Set doesClientReceivePaidCare
     *
     * @param string $doesClientReceivePaidCare
     * @return Report
     */
    public function setDoesClientReceivePaidCare($doesClientReceivePaidCare)
    {
        $this->doesClientReceivePaidCare = $doesClientReceivePaidCare;

        return $this;
    }

    /**
     * Get doesClientReceivePaidCare
     *
     * @return string 
     */
    public function getDoesClientReceivePaidCare()
    {
        return $this->doesClientReceivePaidCare;
    }

    /**
     * Set whoIsDoingTheCaring
     *
     * @param string $whoIsDoingTheCaring
     * @return Report
     */
    public function setWhoIsDoingTheCaring($whoIsDoingTheCaring)
    {
        $this->whoIsDoingTheCaring = $whoIsDoingTheCaring;

        return $this;
    }

    /**
     * Get whoIsDoingTheCaring
     *
     * @return string 
     */
    public function getWhoIsDoingTheCaring()
    {
        return $this->whoIsDoingTheCaring;
    }

    /**
     * Set doesClientHaveACarePlan
     *
     * @param string $doesClientHaveACarePlan
     * @return Report
     */
    public function setDoesClientHaveACarePlan($doesClientHaveACarePlan)
    {
        $this->doesClientHaveACarePlan = $doesClientHaveACarePlan;

        return $this;
    }

    /**
     * Get doesClientHaveACarePlan
     *
     * @return string 
     */
    public function getDoesClientHaveACarePlan()
    {
        return $this->doesClientHaveACarePlan;
    }

    /**
     * Set whenWasCarePlanLastReviewed
     *
     * @param \DateTime $whenWasCarePlanLastReviewed
     * @return Report
     */
    public function setWhenWasCarePlanLastReviewed($whenWasCarePlanLastReviewed)
    {
        $this->whenWasCarePlanLastReviewed = $whenWasCarePlanLastReviewed;

        return $this;
    }

    /**
     * Get whenWasCarePlanLastReviewed
     *
     * @return \DateTime 
     */
    public function getWhenWasCarePlanLastReviewed()
    {
        return $this->whenWasCarePlanLastReviewed;
    }

    /**
     * Set howIsCareFunded
     *
     * @param string $howIsCareFunded
     * @return Report
     */
    public function setHowIsCareFunded($howIsCareFunded)
    {
        $this->howIsCareFunded = $howIsCareFunded;

        return $this;
    }

    /**
     * Get howIsCareFunded
     *
     * @return string 
     */
    public function getHowIsCareFunded()
    {
        return $this->howIsCareFunded;
    }


    /**
     * If deputy lives with client then we don't 
     * all this other responses
     * 
     * @return boolean
     */
    public function keepOnlyRelevantSafeguardingData()
    {
        if($this->doYouLiveWithClient == "yes"){
            $this->howOftenDoYouVisit = null;
            $this->howOftenDoYouPhoneOrVideoCall = null;
            $this->howOftenDoesClientSeeOtherPeople = null;
            $this->howOftenDoYouWriteEmailOrLetter = null;
        }

        if($this->doesClientReceivePaidCare == "no"){
            $this->howIsCareFunded = null;
        }

        if($this->doesClientHaveACarePlan == "no"){
            $this->whenWasCarePlanLastReviewed = null;
        }
        return true;
    }

    /**
     * checks if report is missing safeguarding
     * information
     * 
     * @return boolean
     */
    public function missingSafeguardingInfo()
    {
        if(empty($this->doYouLiveWithClient) || empty($this->doesClientReceivePaidCare) || empty($this->whoIsDoingTheCaring) || empty($this->doesClientHaveACarePlan)){
            return true;
        }
        return false;
    }
    
    /**
     * @return boolean $noAssetToAdd
     */
    public function getNoAssetToAdd()
    {
        return $this->noAssetToAdd;
    }
    
    /**
     * 
     * @param boolean $noAssetToAdd
     * @return \AppBundle\Entity\Report
     */
    public function setNoAssetToAdd($noAssetToAdd)
    {
        $this->noAssetToAdd = $noAssetToAdd;
        return $this;
    }
    
    /**
     * @return boolean $submitted
     */
    public function getSubmitted()
    {
        return $this->submitted;
    }
    
    /**
     * @param type $submitted
     * @return \AppBundle\Entity\Report
     */
    public function setSubmitted($submitted)
    {
        $this->submitted = $submitted;
        return $this;
    }
    
    /**
     * @return boolean
     */
    public function getReviewed()
    {
        return $this->reviewed;
    }

    /**
     * @param boolean $reviewed
     */
    public function setReviewed($reviewed)
    {
        $this->reviewed = $reviewed;
    }
        
    /**
     * @return string
     */
    public function getFurtherInformation()
    {
        return $this->furtherInformation;
    }

    /**
     * @param string $furtherInformation
     */
    public function setFurtherInformation($furtherInformation)
    {
        $this->furtherInformation = $furtherInformation;
    }
    
    /**
     * @param type $reportSeen
     * @return \AppBundle\Entity\Report
     */
    public function setReportSeen($reportSeen)
    {
        $this->reportSeen = $reportSeen;
    }

    /**
     * @return type
     */
    public function getReportSeen()
    {
        return $this->reportSeen;
    }
    
    /**
     * @return string $status | null
     */
    public function getStatus()
    {
        if(!$this->isDue()){
            return 'in progress';
        }
        
        if($this->isDue() && !$this->submitted){
            return 'in review';
        }
        
        if($this->submitted){
            return 'submitted';
        }
        return null;
    }
}