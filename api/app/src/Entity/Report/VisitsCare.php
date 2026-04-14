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
     * @var ?string yes|no|null
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"visits-care"})
     *
     * @ORM\Column(name="do_you_live_with_client", type="string", length=4, nullable=true)
     */
    private $doYouLiveWithClient;

    /**
     * @var ?string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"visits-care"})
     *
     * @ORM\Column(name="how_often_contact_client", type="text", nullable=true)
     */
    private $howOftenDoYouContactClient;

    /**
     * @var ?string yes|no|null
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"visits-care"})
     *
     * @ORM\Column( name="does_client_receive_paid_care", type="text", nullable=true)
     */
    private $doesClientReceivePaidCare;

    /**
     * @var ?string client_pays_for_all | client_gets_financial_help | all_care_is_paid_by_someone_else
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"visits-care"})
     *
     * @ORM\Column(name="how_is_care_funded", length=255, type="string", nullable=true)
     */
    private $howIsCareFunded;

    /**
     * @var ?string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"visits-care"})
     *
     * @ORM\Column( name="who_is_doing_the_caring", type="text", nullable=true)
     */
    private $whoIsDoingTheCaring;

    /**
     * @var ?string yes|no|null
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
     * @var ?\DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @JMS\Groups({"visits-care"})
     *
     * @ORM\Column(name="when_was_care_plan_last_reviewed", type="date", nullable=true, options={ "default": null })
     */
    private $whenWasCarePlanLastReviewed;

    public function getId(): int
    {
        return $this->id;
    }

    public function setReport(?Report $report = null): static
    {
        $this->report = $report;

        return $this;
    }

    public function getReport(): Report
    {
        return $this->report;
    }

    public function setDoYouLiveWithClient(?string $doYouLiveWithClient): static
    {
        $this->doYouLiveWithClient = $doYouLiveWithClient;

        return $this;
    }

    public function getDoYouLiveWithClient(): ?string
    {
        return $this->doYouLiveWithClient;
    }

    public function setDoesClientReceivePaidCare(?string $doesClientReceivePaidCare): static
    {
        $this->doesClientReceivePaidCare = $doesClientReceivePaidCare;

        return $this;
    }

    public function getDoesClientReceivePaidCare(): ?string
    {
        return $this->doesClientReceivePaidCare;
    }

    public function setWhoIsDoingTheCaring(?string $whoIsDoingTheCaring): static
    {
        $this->whoIsDoingTheCaring = $whoIsDoingTheCaring;

        return $this;
    }

    public function getWhoIsDoingTheCaring(): ?string
    {
        return $this->whoIsDoingTheCaring;
    }

    public function setDoesClientHaveACarePlan(?string $doesClientHaveACarePlan): static
    {
        $this->doesClientHaveACarePlan = $doesClientHaveACarePlan;

        return $this;
    }

    public function getDoesClientHaveACarePlan(): ?string
    {
        return $this->doesClientHaveACarePlan;
    }

    public function setWhenWasCarePlanLastReviewed(?\DateTime $whenWasCarePlanLastReviewed): static
    {
        $this->whenWasCarePlanLastReviewed = $whenWasCarePlanLastReviewed;

        return $this;
    }

    public function getWhenWasCarePlanLastReviewed(): ?\DateTime
    {
        return $this->whenWasCarePlanLastReviewed;
    }

    public function setHowIsCareFunded(?string $howIsCareFunded): static
    {
        $this->howIsCareFunded = $howIsCareFunded;

        return $this;
    }

    public function getHowIsCareFunded(): ?string
    {
        return $this->howIsCareFunded;
    }

    public function getHowOftenDoYouContactClient(): ?string
    {
        return $this->howOftenDoYouContactClient;
    }

    public function setHowOftenDoYouContactClient(?string $howOftenDoYouContactClient): static
    {
        $this->howOftenDoYouContactClient = $howOftenDoYouContactClient;

        return $this;
    }
}
