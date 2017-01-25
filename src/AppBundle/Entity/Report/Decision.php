<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Report\Traits\HasReportTrait;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\XmlRoot("decision")
 */
class Decision
{
    use HasReportTrait;

    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"decision"})
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"decision"})
     *
     * @Assert\NotBlank( message="decision.description.notBlank", groups={"decision-description"})
     * @Assert\Length( min=2, minMessage="decision.description.length", groups={"decision-description"})
     *
     * @var string
     */
    private $description;

    /**
     * @Assert\NotBlank( message="decision.clientInvolvedBoolean.notBlank", groups={"decision-client-involved"})
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"decision"})
     *
     * @var bool
     */
    private $clientInvolvedBoolean;

    /**
     * @Assert\NotBlank( message="decision.clientInvolvedDetails.notBlank", groups={"decision-client-involved-details"})
     * @Assert\Length( min=2, minMessage="decision.clientInvolvedDetails.length", groups={"decision-client-involved-details"})
     *
     * @JMS\Type("string")
     * @JMS\Groups({"decision"})
     *
     * @var bool
     */
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
     *
     * @return Decision
     */
    public function setId($id)
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
     *
     * @return Decision
     */
    public function setDescription($description)
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
     *
     * @return Decision
     */
    public function setClientInvolvedBoolean($clientInvolvedBoolean)
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
     *
     * @return Decision
     */
    public function setClientInvolvedDetails($clientInvolvedDetails)
    {
        $this->clientInvolvedDetails = $clientInvolvedDetails;

        return $this;
    }
}
