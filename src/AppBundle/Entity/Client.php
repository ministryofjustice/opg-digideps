<?php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\XmlRoot("client")
 */
class Client
{
    /**
     * @JMS\Type("integer")
     * @var integer
     */
    private $id;
    
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank( message="client.firstname.notBlank" )
     * @Assert\Length(min = 2, minMessage= "client.firstname.minMessage")
     * @var string $firstname
     */
    private $firstname;
    
    /**
     *
     * @JMS\Type("integer")
     * @var array $user
     */
    private $user;
    
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank( message="client.lastname.notBlank" )
     * @Assert\Length(min = 2, minMessage= "client.lastname.minMessage")
     * @var string $lastname
     */
    private $lastname;
    
    /**
     * @JMS\Type("string")
     * @var string $caseNumber
     */
    private $caseNumber;
    
    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @Assert\NotBlank( message="client.courtDate.notBlank")
     * @Assert\Date( message="client.courtDate.message")
     * @var array $courtDate
     */
    private $courtDate;
    
    /**
     * @JMS\Type("array")
     * @Assert\NotBlank( message = "client.allowedCourtOrderTypes.notBlank")
     * @var array allowedCourtOrderTypes
     */
    private $allowedCourtOrderTypes;
    
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank( message="client.address.notBlank")
     * @var string $address
     */
    private $address;
    
    /**
     * @JMS\Type("string")
     * @var string $address2
     */
    private $address2;
    
    /**
     * @JMS\Type("string")
     * @var string $county
     */
    private $county;
    
    /**
     * @JMS\Type("string")
     * @var string $postcode
     */
    private $postcode;
    
    /**
     * @JMS\Type("string")
     * @var string $country
     */
    private $country;
    
    /**
     * @JMS\Type("string")
     * @var string $phone
     */
    private $phone;
   
    
    public function __construct()
    {
        $this->allowedCourtOrderTypes = [];
        //$this->users = [];
    }
    
    /**
     * 
     * @return string $firstname
     */
    public function getFirstname()
    {
        return $this->firstname;
    }
    
    /**
     * 
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }
    
    
    public function getUser()
    {
        return $this->user;
    }
    
    
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }
    
    /**
     * 
     * @return string $lastname
     */
    public function getLastname()
    {
        return $this->lastname;
    }
    
    /**
     *
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }
    
    /**
     * 
     * @return string $caseNumber
     */
    public function getCaseNumber()
    {
        return $this->caseNumber;
    }
    
    /**
     * 
     * @param string $caseNumber
     */
    public function setCaseNumber($caseNumber)
    {
        $this->caseNumber = $caseNumber;
    }
    
    /**
     * 
     * @return array $courtDate
     */
    public function getCourtDate()
    {
        return $this->courtDate;
    }
    
    /**
     * 
     * @param array $courtDate
     */
    public function setCourtDate($courtDate)
    {
        $this->courtDate = $courtDate;
    }
    
    /**
     * 
     * @return array $allowdCourtOrderTypes
     */
    public function getAllowedCourtOrderTypes()
    {
       return $this->allowedCourtOrderTypes; 
    }
    
    /**
     * 
     * @param array $allowedCourtOrderType
     */
    public function addAllowedCourtOrderType($allowedCourtOrderType)
    {
        $this->allowedCourtOrderTypes[] = $allowedCourtOrderType;
    }
    
    /**
     * @param array $allowedCourtOrderType
     * @return boolean
     */
    public function removeAllowedCourtOrderType($allowedCourtOrderType)
    {
        $key = array_search($allowedCourtOrderType, $this->allowedCourtOrderTypes);
        
        if($key){
            unset($this->allowedCourtOrderTypes[$key]);
            return true;
        }
        return false;
    }
    
    /**
     * 
     * @return string $address
     */
    public function getAddress()
    {
        return $this->address;
    }
    
    /**
     * 
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }
    
    /**
     * 
     * @return string $address2
     */
    public function getAddress2()
    {
        return $this->address2;
    }
    
    /**
     * 
     * @param string $address2
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;
    }
    
    /**
     * 
     * @return string $county
     */
    public function getCounty()
    {
        return $this->county;
    }
    
    /**
     * 
     * @param string $county
     */
    public function setCounty($county)
    {
        $this->county = $county;
    }
    
    /**
     * 
     * @return string $postcode
     */
    public function getPostcode()
    {
        return $this->postcode;
    }
    
    /**
     * 
     * @param string $postcode
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;
    }
    
    /**
     * 
     * @return string $country
     */
    public function getCountry()
    {
        return $this->country;
    }
    
    /**
     * 
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }
    
    /**
     * 
     * @return string $phone
     */
    public function getPhone()
    {
        return $this->phone;
    }
    
    /**
     * 
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

}
