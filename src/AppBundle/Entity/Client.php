<?php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @JMS\XmlRoot("client")
 * @JMS\ExclusionPolicy("none")
 * @Assert\Callback(methods={"isValidCourtDate"})
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
     * @JMS\Accessor(setter="addUsers", getter="getUsers")
     * @JMS\Type("array")
     * @var array $users
     */
    private $users;
    
    /**
     * @JMS\Accessor(setter="addReports")
     * @JMS\Type("array")
     * @var array $reports
     */
    private $reports;
    
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank( message="client.lastname.notBlank" )
     * @Assert\Length(min = 2, minMessage= "client.lastname.minMessage")
     * @var string $lastname
     */
    private $lastname;
    
    /**
     * @JMS\Exclude()
     * @var string
     */
    private $fullname;
    
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank( message="client.caseNumber.notBlank")
     * @var string $caseNumber
     */
    private $caseNumber;
    
    /**
     * @JMS\Accessor(setter="setCourtDateWithoutTime")
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @Assert\NotBlank( message="client.courtDate.notBlank")
     * @Assert\Date( message="client.courtDate.message")
     * @var array $courtDate
     */ 
    private $courtDate;
    
    /**
     * @JMS\Accessor(setter="setAllowedCourtOrderTypes")
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
     * @Assert\NotBlank( message="client.postcode.notBlank")
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
     * @Assert\Length(min=10, max=25, minMessage="common.genericPhone.minLength", maxMessage="common.genericPhone.maxLength")
     * @var string $phone
     */
    private $phone;
   
    
    public function __construct()
    {
        $this->allowedCourtOrderTypes = [];
        $this->users = [];
        $this->reports = [];
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
    
    
    public function getUsers()
    {
        return $this->users;
    }
    
    
    public function addUsers($users)
    {
        $this->users = $users;
        return $this;
    }
    
    public function addUser($user)
    {
        $this->users[] = $user;
        return $this;
    }
    
    /**
     * @return array $reports
     */
    public function getReports()
    {
        return $this->reports;
    }
    
    /**
     * @param  $report
     * @return \AppBundle\Entity\Client
     */
    public function addReport($report)
    {
        $this->reports[] = $report;
        return $this;
    }
    
    /**
     * @param type $reports
     * @return \AppBundle\Entity\Client
     */
    public function addReports($reports)
    {
        $this->reports = $reports;
        return $this;
    }
    
    public function removeReport($report)
    {
        if(!empty($this->reports)){
            foreach($this->reports as $key => $reportObj){
                if($reportObj->getId() == $report->getId()){
                    unset($this->reports[$key]);
                    return $this;
                }
            }
        }
        return $this;
    }
    
    public function hasReport()
    {
        if(!empty($this->reports)){
            return true;
        }
        return false;
    }
    /**
     * 
     * @return string $lastname
     */
    public function getLastname()
    {
        return $this->lastname;
    }
    
    public function getFullname()
    {
        $this->fullname = $this->firstname.' '.$this->lastname;
        return $this->fullname;
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
    
    public function setCourtDateWithoutTime($courtDate)
    {
        $this->courtDate = new \DateTime($courtDate->format('Y-m-d'));
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
    
    public function setAllowedCourtOrderTypes($allowedCourtOrderTypes)
    {
       $this->allowedCourtOrderTypes = $allowedCourtOrderTypes; 
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
    
    public function isValidCourtDate(ExecutionContextInterface $context)
    {
        $today = new \DateTime();
        
        if($this->courtDate > $today){
            $context->addViolationAt('courtDate','Court Date cannot be in the future');
        }
    }

}
