<?php

namespace App\Entity\Ndr;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="odr_visits_care")
 */
class VisitsCare
{
    /**
     * @var int
     *
     *
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\SequenceGenerator(sequenceName="odr_visits_care_id_seq", allocationSize=1, initialValue=1)
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['visits-care'])]
    private $id;

    /**
     * @var Ndr
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Ndr\Ndr", inversedBy="visitsCare")
     *
     * @ORM\JoinColumn(name="odr_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $ndr;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="plan_move_residence", type="string", length=4, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['visits-care'])]
    private $planMoveNewResidence;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="plan_move_residence_details", type="text", nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['visits-care'])]
    private $planMoveNewResidenceDetails;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="do_you_live_with_client", type="string", length=4, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['visits-care'])]
    private $doYouLiveWithClient;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="how_often_contact_client", type="text", nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['visits-care'])]
    private $howOftenDoYouContactClient;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column( name="does_client_receive_paid_care", type="text", nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['visits-care'])]
    private $doesClientReceivePaidCare;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="how_is_care_funded", length=255, type="string", nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['visits-care'])]
    private $howIsCareFunded;

    /**
     * @var type
     *
     *
     *
     * @ORM\Column( name="who_is_doing_the_caring", type="text", nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['visits-care'])]
    private $whoIsDoingTheCaring;

    /**
     * @var type
     *
     *
     *
     *
     * @ORM\Column( name="does_client_have_a_care_plan", type="string", length=4, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['visits-care'])]
    #[JMS\SerializedName('does_client_have_a_care_plan')]
    private $doesClientHaveACarePlan;

    /**
     * @var date
     *
     *
     *
     * @ORM\Column(name="when_was_care_plan_last_reviewed", type="date", nullable=true, options={ "default": null })
     */
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['visits-care'])]
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
     * @return mixed
     */
    public function getNdr()
    {
        return $this->ndr;
    }

    /**
     * @param mixed $ndr
     *
     * @return VisitsCare
     */
    public function setNdr(Ndr $ndr)
    {
        $this->ndr = $ndr;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlanMoveNewResidence()
    {
        return $this->planMoveNewResidence;
    }

    /**
     * @param string $planMoveNewResidence
     *
     * @return VisitsCare
     */
    public function setPlanMoveNewResidence($planMoveNewResidence)
    {
        $this->planMoveNewResidence = $planMoveNewResidence;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlanMoveNewResidenceDetails()
    {
        return $this->planMoveNewResidenceDetails;
    }

    /**
     * @param string $planMoveNewResidenceDetails
     */
    public function setPlanMoveNewResidenceDetails($planMoveNewResidenceDetails)
    {
        $this->planMoveNewResidenceDetails = $planMoveNewResidenceDetails;
    }

    /**
     * @return string
     */
    public function getDoYouLiveWithClient()
    {
        return $this->doYouLiveWithClient;
    }

    /**
     * @param string $doYouLiveWithClient
     *
     * @return VisitsCare
     */
    public function setDoYouLiveWithClient($doYouLiveWithClient)
    {
        $this->doYouLiveWithClient = $doYouLiveWithClient;

        return $this;
    }

    /**
     * @return string
     */
    public function getHowOftenDoYouContactClient()
    {
        return $this->howOftenDoYouContactClient;
    }

    /**
     * @param string $howOftenDoYouContactClient
     */
    public function setHowOftenDoYouContactClient($howOftenDoYouContactClient)
    {
        $this->howOftenDoYouContactClient = $howOftenDoYouContactClient;
    }

    /**
     * @return string
     */
    public function getDoesClientReceivePaidCare()
    {
        return $this->doesClientReceivePaidCare;
    }

    /**
     * @param string $doesClientReceivePaidCare
     *
     * @return VisitsCare
     */
    public function setDoesClientReceivePaidCare($doesClientReceivePaidCare)
    {
        $this->doesClientReceivePaidCare = $doesClientReceivePaidCare;

        return $this;
    }

    /**
     * @return string
     */
    public function getHowIsCareFunded()
    {
        return $this->howIsCareFunded;
    }

    /**
     * @param string $howIsCareFunded
     */
    public function setHowIsCareFunded($howIsCareFunded)
    {
        $this->howIsCareFunded = $howIsCareFunded;
    }

    /**
     * @return type
     */
    public function getWhoIsDoingTheCaring()
    {
        return $this->whoIsDoingTheCaring;
    }

    /**
     * @param string $whoIsDoingTheCaring
     *
     * @return VisitsCare
     */
    public function setWhoIsDoingTheCaring($whoIsDoingTheCaring)
    {
        $this->whoIsDoingTheCaring = $whoIsDoingTheCaring;

        return $this;
    }

    /**
     * @return type
     */
    public function getDoesClientHaveACarePlan()
    {
        return $this->doesClientHaveACarePlan;
    }

    /**
     * @param string $doesClientHaveACarePlan
     *
     * @return VisitsCare
     */
    public function setDoesClientHaveACarePlan($doesClientHaveACarePlan)
    {
        $this->doesClientHaveACarePlan = $doesClientHaveACarePlan;

        return $this;
    }

    /**
     * @return date
     */
    public function getWhenWasCarePlanLastReviewed()
    {
        return $this->whenWasCarePlanLastReviewed;
    }

    /**
     * @param date $whenWasCarePlanLastReviewed
     */
    public function setWhenWasCarePlanLastReviewed($whenWasCarePlanLastReviewed)
    {
        $this->whenWasCarePlanLastReviewed = $whenWasCarePlanLastReviewed;
    }
}
