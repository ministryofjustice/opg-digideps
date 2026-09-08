<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Report\Traits\HasReportTrait;
use Symfony\Component\Validator\Constraints as Assert;

class Decision
{
    use HasReportTrait;

    #[JMS\Type('integer')]
    #[JMS\Groups(['decision'])]
    private ?int $id = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['decision'])]
    #[Assert\NotBlank(message: 'decision.description.notBlank', groups: ['decision-description'])]
    #[Assert\Length(min: 2, minMessage: 'decision.description.length', groups: ['decision-description'])]
    private ?string $description = null;

    // NB Symfony handles the type shifting between string and bool; trying to enforce one type
    // breaks the decision form checkbox
    #[JMS\Type('boolean')]
    #[JMS\Groups(['decision'])]
    #[Assert\NotBlank(message: 'decision.clientInvolvedBoolean.notBlank', groups: ['decision-client-involved'])]
    private bool|string|null $clientInvolvedBoolean = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['decision'])]
    #[Assert\NotBlank(message: 'decision.clientInvolvedDetails.notBlank', groups: ['decision-client-involved-details'])]
    #[Assert\Length(min: 2, minMessage: 'decision.clientInvolvedDetails.length', groups: ['decision-client-involved-details'])]
    private ?string $clientInvolvedDetails = null;

    #[JMS\Type('DateTime')]
    #[JMS\Groups(['decision'])]
    /* @phpstan-ignore property.unusedType */
    private ?\DateTime $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function isClientInvolvedBoolean(): bool|string|null
    {
        return $this->clientInvolvedBoolean;
    }

    public function setClientInvolvedBoolean(bool|string|null $clientInvolvedBoolean): static
    {
        $this->clientInvolvedBoolean = $clientInvolvedBoolean;
        return $this;
    }

    public function isClientInvolvedDetails(): ?string
    {
        return $this->clientInvolvedDetails;
    }

    public function setClientInvolvedDetails(?string $clientInvolvedDetails): static
    {
        $this->clientInvolvedDetails = $clientInvolvedDetails;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }
}
