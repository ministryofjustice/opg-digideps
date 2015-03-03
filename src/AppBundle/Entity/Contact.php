<?php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\XmlRoot("contact")
 */
class Contact
{
    /**
     * @JMS\Type("integer")
     * @var integer $id
     */
    private $id;
    
    /**
     * @Assert\NotBlank( message="contact.name.notBlank" )
     * @Assert\Type( type="string", message="contact.name.type")
     * @Assert\Length( min=2, minMessage="contact.name.length")
     * @JMS\Type("string")
     * @var string $contactName
     */
    private $contactName;
    
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank( message="contact.address.notBlank")
     */
    private $address;
    
    /**
     * @JMS\Type("string")
     */
    private $address2;
    
    /**
     * @JMS\Type("string")
     */
    private $county;
    
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank( message="contact.postcode.notBlank")
     */
    private $postcode;
    
    /**
     *
     * @JMS\Type("string")
     */
    private $country;
    
    /**
     *
     * @JMS\Type("string")
     * @Assert\notBlank( message="contact.explanation.notBlank" )
     * @Assert\Type( type="string", message="contact.explanation.type")
     * @Assert\Length( min=5, minMessage="contact.explanation.length")
     */
    private $explanation;
    
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank( message="contact.relationship.notBlank" )
     * @Assert\Type( type="string", message="contact.relationship.type")
     * @Assert\Length( min = 5, minMessage="contact.relationship.length")
     */
    private $relationship;
    
    /**
     * @JMS\Type("string")
     */
    private $phone;
    
    /**
     *
     * @JMS\Type("integer")
     */
    private $report;
    
    
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
    
    public function getReport()
    {
        return $this->report;
    }
    
    public function setReport($report)
    {
        $this->report = $report;
        return $this;
    }
    
}