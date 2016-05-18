<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\XmlRoot("decision")
 */
class Decision
{
    use Traits\HasReportTrait;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank( message="decision.description.notBlank" )
     * @Assert\Length( min=2, minMessage="decision.description.length")
     *
     * @var string
     */
    private $description;

    /**
     * @Assert\NotBlank( message="decision.clientInvolvedBoolean.notBlank")
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $clientInvolvedBoolean;

    /**
     * @Assert\NotBlank( message="decision.clientInvolvedDetails.notBlank")
     * @Assert\Length( min=2, minMessage="decision.clientInvolvedDetails.length")
     * @JMS\Type("string")
     *
     * @var bool
     */
    private $clientInvolvedDetails;

    public function getId()
    {
        return $this->id;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setClientInvolvedBoolean($clientInvolvedBoolean)
    {
        $this->clientInvolvedBoolean = $clientInvolvedBoolean;
    }

    public function getClientInvolvedBoolean()
    {
        return $this->clientInvolvedBoolean;
    }

    public function setClientInvolvedDetails($clientInvolvedDetails)
    {
        $this->clientInvolvedDetails = $clientInvolvedDetails;
    }

    public function getClientInvolvedDetails()
    {
        return $this->clientInvolvedDetails;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }
}
