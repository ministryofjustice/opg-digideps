<?php

namespace AppBundle\Entity\Odr;

use AppBundle\Entity\Odr\Traits\HasOdrTrait;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

class VisitsCare
{
    use HasOdrTrait;

    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"visits-care"})
     *
     * @var int
     */
    private $id;



    /**
     * @JMS\Type("string")
     * @JMS\Groups({"visits-care"})
     * @Assert\NotBlank(message="odr.visitsCare.doYouLiveWithClient.notBlank", groups={"visits-care-live-client"})
     */
    private $doYouLiveWithClient;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"visits-care"})
     * @Assert\NotBlank(message="odr.visitsCare.howOftenDoYouContactClient.notBlank", groups={"visits-care-how-often-contact"})
     */
    private $howOftenDoYouContactClient;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"visits-care"})
     * @Assert\NotBlank(message="odr.visitsCare.doesClientReceivePaidCare.notBlank", groups={"visits-care-receive-paid-care"})
     */
    private $doesClientReceivePaidCare;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"visits-care"})
     * @Assert\NotBlank(message="odr.visitsCare.howIsCareFunded.notBlank", groups={"visits-care-how-care-funded"})
     */
    private $howIsCareFunded;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"visits-care"})
     * @Assert\NotBlank(message="odr.visitsCare.whoIsDoingTheCaring.notBlank", groups={"visits-care-who-does-caring"})
     */
    private $whoIsDoingTheCaring;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"visits-care"})
     * @Assert\NotBlank(message="odr.visitsCare.doesClientHaveACarePlan.notBlank", groups={"visits-care-have-care-plan"})
     */
    private $doesClientHaveACarePlan;

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"visits-care"})
     * @Assert\NotBlank(message="odr.visitsCare.whenWasCarePlanLastReviewed.notBlank", groups={"visits-care-care-plan-last-review"})
     * @Assert\Date( message="odr.visitsCare.whenWasCarePlanLastReviewed.invalidMessage", groups={"visits-care-care-plan-last-review"} )
     */
    private $whenWasCarePlanLastReviewed;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"visits-care"})
     * @Assert\NotBlank(message="odr.visitsCare.planMoveNewResidence.notBlank", groups={"visits-care-plan-move-residence"})
     */
    private $planMoveNewResidence;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"visits-care"})
     * @Assert\NotBlank(message="odr.visitsCare.planMoveNewResidenceDetails.notBlank", groups={"visits-care-plan-move-residence-details"})
     */
    private $planMoveNewResidenceDetails;

    /**
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPlanMoveNewResidence()
    {
        return $this->planMoveNewResidence;
    }

    /**
     * @param mixed $planMoveNewResidence
     */
    public function setPlanMoveNewResidence($planMoveNewResidence)
    {
        $this->planMoveNewResidence = $planMoveNewResidence;
    }

    /**
     * @return mixed
     */
    public function getPlanMoveNewResidenceDetails()
    {
        return $this->planMoveNewResidenceDetails;
    }

    /**
     * @param mixed $planMoveNewResidenceDetails
     */
    public function setPlanMoveNewResidenceDetails($planMoveNewResidenceDetails)
    {
        $this->planMoveNewResidenceDetails = $planMoveNewResidenceDetails;
    }

    /**
     * Set doYouLiveWithClient.
     *
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
     * Get doYouLiveWithClient.
     *
     * @return string
     */
    public function getDoYouLiveWithClient()
    {
        return $this->doYouLiveWithClient;
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
     * Set doesClientReceivePaidCare.
     *
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
     * @return VisitsCare
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
     * @return VisitsCare
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
     * @return VisitsCare
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
     * @return VisitsCare
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

    /**
     * If deputy lives with client then we don't
     * all this other responses.
     *
     * @return bool
     */
    public function keepOnlyRelevantData()
    {
        if ($this->doYouLiveWithClient == 'yes') {
            $this->howOftenDoYouContactClient = null;
        }

        if ($this->doesClientReceivePaidCare == 'no') {
            $this->howIsCareFunded = null;
        }

        if ($this->doesClientHaveACarePlan == 'no') {
            $this->whenWasCarePlanLastReviewed = null;
        }

        return true;
    }

    /**
     * If deputy lives with client then we don't
     * all this other responses.
     *
     * @return bool
     */
    public function keepOnlyRelevantVisitsCareData()
    {
        if ($this->doYouLiveWithClient == 'yes') {
            $this->howOftenDoYouContactClient = null;
        }

        if ($this->doesClientReceivePaidCare == 'no') {
            $this->howIsCareFunded = null;
        }

        if ($this->doesClientHaveACarePlan == 'no') {
            $this->whenWasCarePlanLastReviewed = null;
        }

        return true;
    }

    /**
     * checks if report is missing visits care
     * information.
     *
     * @return bool
     */
    public function missingInfo()
    {
        if (empty($this->doYouLiveWithClient)
            || empty($this->doesClientReceivePaidCare)
            || empty($this->whoIsDoingTheCaring)
            || empty($this->doesClientHaveACarePlan)
            || empty($this->planMoveNewResidence)
        ) {
            return true;
        }

        return false;
    }
}
