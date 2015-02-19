<?php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Client
{
    /**
     * @Assert\NotBlank( message="registration.firstname.notBlank" )
     * @Assert\Length(min = 2, minMessage= "registration.firstname.minMessage")
     * @var string $firstname
     */
    private $firstname;
    
    /**
     *
     * @var string $lastname
     */
    private $lastname;
    
    /**
     *
     * @var string $caseNumber
     */
    private $caseNumber;
    
    /**
     *
     * @var array $courtDate
     */
    private $courtDate;
    
    /**
     *
     * @var array allowedCourtOrderTypes
     */
    private $allowedCourtOrderTypes;
    
    /**
     *
     * @var string $address
     */
    private $address;
    
    /**
     *
     * @var string $address2
     */
    private $address2;
    
    /**
     *
     * @var string $county
     */
    private $county;
    
    /**
     *
     * @var string $postcode
     */
    private $postcode;
    
    /**
     *
     * @var string $country
     */
    private $country;
    
    /**
     *
     * @var string $phone
     */
    private $phone;
    
    public function __construct()
    {
        $this->allowedCourtOrderTypes = [];
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
}
