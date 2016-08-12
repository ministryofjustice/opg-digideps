<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Traits\HasReportTrait;
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
     * @Assert\NotBlank( message="decision.description.notBlank" )
     * @Assert\Length( min=2, minMessage="decision.description.length")
     *
     * @var string
     */
    private $description;

    /**
     * @Assert\NotBlank( message="decision.clientInvolvedBoolean.notBlank")
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"decision"})
     *
     * @var bool
     */
    private $clientInvolvedBoolean;

    /**
     * @Assert\NotBlank( message="decision.clientInvolvedDetails.notBlank")
     * @Assert\Length( min=2, minMessage="decision.clientInvolvedDetails.length")
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
     * @return Decision
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isClientInvolvedBoolean()
    {
        return $this->clientInvolvedBoolean;
    }

    /**
     * @param boolean $clientInvolvedBoolean
     * @return Decision
     */
    public function setClientInvolvedBoolean($clientInvolvedBoolean)
    {
        $this->clientInvolvedBoolean = $clientInvolvedBoolean;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isClientInvolvedDetails()
    {
        return $this->clientInvolvedDetails;
    }

    /**
     * @param boolean $clientInvolvedDetails
     * @return Decision
     */
    public function setClientInvolvedDetails($clientInvolvedDetails)
    {
        $this->clientInvolvedDetails = $clientInvolvedDetails;
        return $this;
    }


}
