<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Traits\HasReportTrait;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\XmlRoot("contact")
 */
class Contact
{
    use HasReportTrait;

    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"contact"})
     *
     * @var int
     */
    private $id;

    /**
     * @Assert\NotBlank( message="contact.name.notBlank" )
     * @Assert\Length( min=2, minMessage="contact.name.minMessage", max=255, maxMessage="contact.name.maxMessage")
     *
     * @JMS\SerializedName("contact_name")
     * @JMS\Type("string")
     * @JMS\Groups({"contact"})
     *
     * @var string
     */
    private $contactName;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"contact"})
     *
     * @Assert\Length( max=200, maxMessage="contact.address.maxMessage")
     */
    private $address;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"contact"})
     *
     * @Assert\Length( max=200, maxMessage="contact.address.maxMessage")
     */
    private $address2;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"contact"})
     * @Assert\Length( max=200, maxMessage="contact.address.maxMessage")
     */
    private $county;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"contact"})
     *
     * @Assert\Length( max=10, maxMessage="contact.postcode.maxMessage")
     */
    private $postcode;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"contact"})
     */
    private $country;

    /**
     * Reason for contact.
     * 
     * @JMS\Type("string")
     * @JMS\Groups({"contact"})
     *
     * @Assert\notBlank( message="contact.explanation.notBlank" )
     * @Assert\Length( min=6, minMessage="contact.explanation.length")
     */
    private $explanation;

    /**
     * Relationship to the client.
     * 
     * @JMS\Type("string")
     * @JMS\Groups({"contact"})
     *
     * @Assert\NotBlank( message="contact.relationship.notBlank" )
     * @Assert\Length( min = 2, minMessage="contact.relationship.minMessage", max=100, maxMessage="contact.relationship.maxMessage")
     */
    private $relationship;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"contact"})
     *
     * @Assert\Length( max=20, maxMessage="contact.phone.maxMessage")
     */
    private $phone;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getContactName()
    {
        return $this->contactName;
    }

    public function setContactName($contactName)
    {
        $this->contactName = $contactName;

        return $this;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    public function getAddress2()
    {
        return $this->address2;
    }

    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    public function getCounty()
    {
        return $this->county;
    }

    public function setCounty($county)
    {
        $this->county = $county;

        return $this;
    }

    public function getPostcode()
    {
        return $this->postcode;
    }

    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    public function getExplanation()
    {
        return $this->explanation;
    }

    public function setExplanation($explanation)
    {
        $this->explanation = $explanation;

        return $this;
    }

    public function getRelationship()
    {
        return $this->relationship;
    }

    public function setRelationship($relationship)
    {
        $this->relationship = $relationship;

        return $this;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }
}
