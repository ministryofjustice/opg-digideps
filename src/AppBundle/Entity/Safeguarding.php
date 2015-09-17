<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

use Doctrine\ORM\QueryBuilder;

/**
 * Reports
 * @JMS\XmlRoot("safeguarding")
 * @JMS\ExclusionPolicy("NONE")
 * @ORM\Table(name="safeguarding")
 * @ORM\Entity
 */
class Safeguarding 
{
    /**
     * @var integer
     *
     * @JMS\Groups({"transactions","basic"})
     * @JMS\Type("integer")
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="safeguarding_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Report", inversedBy="safeguarding")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     */
    private $report;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"transactions","basic"})
     * @ORM\Column(name="do_you_live_with_client", type="string", length=4, nullable=true)
     */
    private $doYouLiveWithClient;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"transactions","basic"})
     * @ORM\Column(name="how_often_do_you_visit", type="string", length=55, nullable=true)
     */
    private $howOftenDoYouVisit;

    /**
     *
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"transactions","basic"})
     * @ORM\Column(name="how_often_do_you_phone_or_video_call", type="string", length=55, nullable=true)
     */
    private $howOftenDoYouPhoneOrVideoCall;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"transactions","basic"})
     * @ORM\Column(name="how_often_do_you_write_email_or_letter", type="string", length=55, nullable=true)
     */
    private $howOftenDoYouWriteEmailOrLetter;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"transactions","basic"})
     * @ORM\Column(name="how_often_does_client_see_other_people", type="string", length=55, nullable=true)
     */
    private $howOftenDoesClientSeeOtherPeople;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"transactions","basic"})
     * @ORM\Column(name="anything_else_to_tell", type="text", nullable=true)
     */
    private $anythingElseToTell;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"transactions","basic"})
     * @ORM\Column( name="does_client_receive_paid_care", type="text", nullable=true)
     */
    private $doesClientReceivePaidCare;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"transactions","basic"})
     * @ORM\Column(name="how_is_care_funded", length=255, type="string", nullable=true)
     */
    private $howIsCareFunded;

    /**
     * @var type
     *
     * @JMS\Type("string")
     * @JMS\Groups({"transactions","basic"})
     * @ORM\Column( name="who_is_doing_the_caring", type="text", nullable=true)
     */
    private $whoIsDoingTheCaring;

    /**
     * @var type
     *
     * @JMS\Type("string")
     * @JMS\Groups({"transactions","basic"})
     * @ORM\Column( name="does_client_have_a_care_plan", type="string", length=4, nullable=true)
     */
    private $doesClientHaveACarePlan;

    /**
     * @var date
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"transactions","basic"})
     * @ORM\Column(name="when_was_care_plan_last_reviewed", type="date", nullable=true, options={ "default": null })
     */
    private $whenWasCarePlanLastReviewed;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set report
     *
     * @param \AppBundle\Entity\Report $report
     * @return Contact
     */
    public function setReport(\AppBundle\Entity\Report $report = null)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Get report
     *
     * @return \AppBundle\Entity\Report
     */
    public function getReport()
    {
        return $this->report;
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


}
