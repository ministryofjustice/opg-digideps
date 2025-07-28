<?php

namespace App\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="safeguarding")
 *
 * @ORM\Entity
 */
class VisitsCare
{
    /**
     * @var int
     *
     * @JMS\Groups({"visits-care"})
     *
     * @JMS\Type("integer")
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\SequenceGenerator(sequenceName="safeguarding_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Report\Report", inversedBy="visitsCare")
     *
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $report;

    /**
     * @var string yes|no|null
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"visits-care"})
     *
     * @ORM\Column(name="do_you_live_with_client", type="string", length=4, nullable=true)
     */
    private $doYouLiveWithClient;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"visits-care"})
     *
     * @ORM\Column(name="how_often_contact_client", type="text", nullable=true)
     */
    private $howOftenDoYouContactClient;

    /**
     * @var string yes|no|null
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"visits-care"})
     *
     * @ORM\Column( name="does_client_receive_paid_care", type="text", nullable=true)
     */
    private $doesClientReceivePaidCare;

    /**
     * @var string client_pays_for_all | client_gets_financial_help | all_care_is_paid_by_someone_else
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"visits-care"})
     *
     * @ORM\Column(name="how_is_care_funded", length=255, type="string", nullable=true)
     */
    private $howIsCareFunded;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"visits-care"})
     *
     * @ORM\Column( name="who_is_doing_the_caring", type="text", nullable=true)
     */
    private $whoIsDoingTheCaring;

    /**
     * @var string yes|no|null
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"visits-care"})
     * Setting this due to JMS bug that returned a name of does_client_have_acare_plan
     *
     * @JMS\SerializedName("does_client_have_a_care_plan")
     *
     * @ORM\Column( name="does_client_have_a_care_plan", type="string", length=4, nullable=true)
     */
    private $doesClientHaveACarePlan;

    /**
     * @var DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @JMS\Groups({"visits-care"})
     *
     * @ORM\Column(name="when_was_care_plan_last_reviewed", type="date", nullable=true, options={ "default": null })
     */
    private $whenWasCarePlanLastReviewed;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set report.
     *
     * @return Contact
     */
    public function setReport(?Report $report = null)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Get report.
     *
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * Set doYouLiveWithClient.
     *
     * @param string $doYouLiveWithClient
     *
     * @return Report
     */
    public function setDoYouLiveWithClient($doYouLiveWithClient)
    {
        $this->doYouLiveWithClient = $doYouLiveWithClient;

        return $this;
    }

    /**
     * Get doYouLiveWithClient.
     *
     * @return string
     */
    public function getDoYouLiveWithClient()
    {
        return $this->doYouLiveWithClient;
    }

    /**
     * Set doesClientReceivePaidCare.
     *
     * @param string $doesClientReceivePaidCare
     *
     * @return Report
     */
    public function setDoesClientReceivePaidCare($doesClientReceivePaidCare)
    {
        $this->doesClientReceivePaidCare = $doesClientReceivePaidCare;

        return $this;
    }

    /**
     * Get doesClientReceivePaidCare.
     *
     * @return string
     */
    public function getDoesClientReceivePaidCare()
    {
        return $this->doesClientReceivePaidCare;
    }

    /**
     * Set whoIsDoingTheCaring.
     *
     * @param string $whoIsDoingTheCaring
     *
     * @return Report
     */
    public function setWhoIsDoingTheCaring($whoIsDoingTheCaring)
    {
        $this->whoIsDoingTheCaring = $whoIsDoingTheCaring;

        return $this;
    }

    /**
     * Get whoIsDoingTheCaring.
     *
     * @return string
     */
    public function getWhoIsDoingTheCaring()
    {
        return $this->whoIsDoingTheCaring;
    }

    /**
     * Set doesClientHaveACarePlan.
     *
     * @param string $doesClientHaveACarePlan
     *
     * @return Report
     */
    public function setDoesClientHaveACarePlan($doesClientHaveACarePlan)
    {
        $this->doesClientHaveACarePlan = $doesClientHaveACarePlan;

        return $this;
    }

    /**
     * Get doesClientHaveACarePlan.
     *
     * @return string
     */
    public function getDoesClientHaveACarePlan()
    {
        return $this->doesClientHaveACarePlan;
    }

    /**
     * Set whenWasCarePlanLastReviewed.
     *
     * @param \DateTime $whenWasCarePlanLastReviewed
     *
     * @return Report
     */
    public function setWhenWasCarePlanLastReviewed($whenWasCarePlanLastReviewed)
    {
        $this->whenWasCarePlanLastReviewed = $whenWasCarePlanLastReviewed;

        return $this;
    }

    /**
     * Get whenWasCarePlanLastReviewed.
     *
     * @return \DateTime
     */
    public function getWhenWasCarePlanLastReviewed()
    {
        return $this->whenWasCarePlanLastReviewed;
    }

    /**
     * Set howIsCareFunded.
     *
     * @param string $howIsCareFunded
     *
     * @return Report
     */
    public function setHowIsCareFunded($howIsCareFunded)
    {
        $this->howIsCareFunded = $howIsCareFunded;

        return $this;
    }

    /**
     * Get howIsCareFunded.
     *
     * @return string
     */
    public function getHowIsCareFunded()
    {
        return $this->howIsCareFunded;
    }

    public function getHowOftenDoYouContactClient()
    {
        return $this->howOftenDoYouContactClient;
    }

    public function setHowOftenDoYouContactClient($howOftenDoYouContactClient)
    {
        $this->howOftenDoYouContactClient = $howOftenDoYouContactClient;

        return $this;
    }

    /**
     * checks if report is missing visits care
     * information.
     *
     * @return bool
     */
    public function missingInfo()
    {
        if (empty($this->doYouLiveWithClient) || empty($this->doesClientReceivePaidCare) || empty($this->whoIsDoingTheCaring) || empty($this->doesClientHaveACarePlan)) {
            return true;
        }

        return false;
    }
}
