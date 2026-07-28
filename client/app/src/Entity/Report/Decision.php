<?php

namespace OPG\Digideps\Frontend\Entity\Report;

use OPG\Digideps\Frontend\Entity\Report\Traits\HasReportTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Decision
{
    use HasReportTrait;

    /**
     * @var int
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['decision'])]
    private $id;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['decision'])]
    #[Assert\NotBlank(message: 'decision.description.notBlank', groups: ['decision-description'])]
    #[Assert\Length(min: 2, minMessage: 'decision.description.length', groups: ['decision-description'])]
    private $description;

    /**
     * @var bool
     */
    #[Assert\NotBlank(message: 'decision.clientInvolvedBoolean.notBlank', groups: ['decision-client-involved'])]
    #[JMS\Type('boolean')]
    #[JMS\Groups(['decision'])]
    private $clientInvolvedBoolean;

    /**
     * @var bool
     */
    #[Assert\NotBlank(message: 'decision.clientInvolvedDetails.notBlank', groups: ['decision-client-involved-details'])]
    #[Assert\Length(min: 2, minMessage: 'decision.clientInvolvedDetails.length', groups: ['decision-client-involved-details'])]
    #[JMS\Type('string')]
    #[JMS\Groups(['decision'])]
    private $clientInvolvedDetails;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return bool
     */
    public function isClientInvolvedBoolean()
    {
        return $this->clientInvolvedBoolean;
    }

    /**
     * @param bool $clientInvolvedBoolean
     */
    public function setClientInvolvedBoolean($clientInvolvedBoolean): static
    {
        $this->clientInvolvedBoolean = $clientInvolvedBoolean;

        return $this;
    }

    /**
     * @return bool
     */
    public function isClientInvolvedDetails()
    {
        return $this->clientInvolvedDetails;
    }

    /**
     * @param bool $clientInvolvedDetails
     */
    public function setClientInvolvedDetails($clientInvolvedDetails): static
    {
        $this->clientInvolvedDetails = $clientInvolvedDetails;

        return $this;
    }
}
