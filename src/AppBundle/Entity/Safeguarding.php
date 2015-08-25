<?php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @JMS\XmlRoot("safeguarding")
 * @JMS\ExclusionPolicy("none")
 */
class Safeguarding
{
    /**
     * @JMS\Type("integer")
     * @var integer
     */
    private $id;


    /**
     * Only used to hold the Report object, needed by the validators for date range reasons
     * @JMS\Exclude
     * @var Report
     */
    private $report;
    
    
    
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


    public function setReportId($reportId)
    {
        $this->reportId = $reportId;
    }

    public function getReportId()
    {
        return $this->reportId;
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
    
    
}
