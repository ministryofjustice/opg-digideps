<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Traits\HasReportTrait;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\XmlRoot("safeguarding")
 * @JMS\ExclusionPolicy("none")
 */
class Safeguarding
{
    use HasReportTrait;

    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"safeguarding"})
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"safeguarding"})
     *
     * @Assert\NotBlank(message="safeguarding.doYouLiveWithClient.notBlank", groups={"safeguarding", "safeguarding-step1"})
     */
    private $doYouLiveWithClient;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"safeguarding"})
     *
     * @Assert\NotBlank(message="safeguarding.howOftenDoYouContactClient.notBlank", groups={"safeguarding-live-client-no"})
     */
    private $howOftenDoYouContactClient;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"safeguarding"})
     *
     * @Assert\NotBlank(message="safeguarding.doesClientReceivePaidCare.notBlank", groups={"safeguarding", "safeguarding-step2"})
     */
    private $doesClientReceivePaidCare;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"safeguarding"})
     *
     * @Assert\NotBlank(message="safeguarding.howIsCareFunded.notBlank", groups={"safeguarding-paidCare", "safeguarding-step2"})
     */
    private $howIsCareFunded;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"safeguarding"})
     *
     * @Assert\NotBlank(message="safeguarding.whoIsDoingTheCaring.notBlank", groups={"safeguarding", "safeguarding-step3"})
     */
    private $whoIsDoingTheCaring;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"safeguarding"})
     *
     * @Assert\NotBlank(message="safeguarding.doesClientHaveACarePlan.notBlank", groups={"safeguarding", "safeguarding-step4"})
     */
    private $doesClientHaveACarePlan;

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"safeguarding"})
     *
     * @Assert\NotBlank(message="safeguarding.whenWasCarePlanLastReviewed.notBlank", groups={"safeguarding-hasCarePlan", "safeguarding-step4"})
     * @Assert\Date( message="safeguarding.whenWasCarePlanLastReviewed.invalidMessage", groups={"safeguarding-hasCarePlan", "safeguarding-step4"} )
     */
    private $whenWasCarePlanLastReviewed;

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
     * Set doYouLiveWithClient.
     *
     * @param string $doYouLiveWithClient
     *
     * @return Safeguarding
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
     * @return Safeguarding
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
     * @return Safeguarding
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
     * @return Safeguarding
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
     * @return Safeguarding
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
     * @return Safeguarding
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
    public function keepOnlyRelevantSafeguardingData()
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
     * checks if report is missing safeguarding
     * information.
     *
     * @return bool
     */
    public function missingSafeguardingInfo()
    {
        if (empty($this->doYouLiveWithClient) || empty($this->doesClientReceivePaidCare) || empty($this->whoIsDoingTheCaring) || empty($this->doesClientHaveACarePlan)) {
            return true;
        }

        return false;
    }
}
