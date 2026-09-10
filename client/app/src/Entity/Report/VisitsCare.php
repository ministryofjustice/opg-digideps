<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Report\Traits\HasReportTrait;
use Symfony\Component\Validator\Constraints as Assert;

class VisitsCare
{
    use HasReportTrait;

    #[JMS\Type('integer')]
    #[JMS\Groups(['visits-care'])]
    private ?int $id = null;

    /** @var ?string 'yes'|'no'|null */
    #[JMS\Type('string')]
    #[JMS\Groups(['visits-care'])]
    #[Assert\NotBlank(message: 'visitsCare.doYouLiveWithClient.notBlank', groups: ['visits-care-live-client'])]
    private ?string $doYouLiveWithClient = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['visits-care'])]
    #[Assert\NotBlank(message: 'visitsCare.howOftenDoYouContactClient.notBlank', groups: ['visits-care-how-often-contact'])]
    private ?string $howOftenDoYouContactClient = null;

    /** @var ?string 'yes'|'no'|null */
    #[JMS\Type('string')]
    #[JMS\Groups(['visits-care'])]
    #[Assert\NotBlank(message: 'visitsCare.doesClientReceivePaidCare.notBlank', groups: ['visits-care-receive-paid-care'])]
    private ?string $doesClientReceivePaidCare = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['visits-care'])]
    #[Assert\NotBlank(message: 'visitsCare.howIsCareFunded.notBlank', groups: ['visits-care-how-care-funded'])]
    private ?string $howIsCareFunded = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['visits-care'])]
    #[Assert\NotBlank(message: 'visitsCare.whoIsDoingTheCaring.notBlank', groups: ['visits-care-who-does-caring'])]
    private ?string $whoIsDoingTheCaring = null;

    /** @var ?string 'yes'|'no'|null */
    #[JMS\Type('string')]
    #[JMS\Groups(['visits-care'])]
    #[JMS\SerializedName('does_client_have_a_care_plan')]
    #[Assert\NotBlank(message: 'visitsCare.doesClientHaveACarePlan.notBlank', groups: ['visits-care-have-care-plan'])]
    private ?string $doesClientHaveACarePlan = null;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['visits-care'])]
    #[Assert\NotBlank(message: 'visitsCare.whenWasCarePlanLastReviewed.notBlank', groups: ['visits-care-care-plan-last-review'])]
    #[Assert\Type(type: 'DateTime', message: 'visitsCare.whenWasCarePlanLastReviewed.invalidMessage', groups: ['visits-care-care-plan-last-review'])]
    private ?\DateTime $whenWasCarePlanLastReviewed = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id)
    {
        $this->id = $id;

        return $this;
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

    public function getHowOftenDoYouContactClient(): ?string
    {
        return $this->howOftenDoYouContactClient;
    }

    public function setHowOftenDoYouContactClient(?string $howOftenDoYouContactClient): static
    {
        $this->howOftenDoYouContactClient = $howOftenDoYouContactClient;

        return $this;
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

    public function keepOnlyRelevantVisitsCareData(): bool
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
}
