<?php
namespace AppBundle\Entity;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @codeCoverageIgnore
 * @JMS\XmlRoot("user")
 * @JMS\AccessType("public_method")
 * @JMS\ExclusionPolicy("none")
 */
class User implements AdvancedUserInterface
{   
    const TOKEN_EXPIRE_HOURS = 48;
    
    /**
     * @JMS\Type("integer")
     * @var integer $id
     */
    private $id;
    
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full", "user_details_basic", "admin_add_user"})
     * @Assert\NotBlank( message="user.firstname.notBlank", groups={"admin_add_user", "user_details_basic", "user_details_full"} )
     * @Assert\Length(min=2, max=50, minMessage="user.firstname.minLength", maxMessage="user.firstname.maxLength", groups={"admin_add_user", "user_details_basic", "user_details_full"} )
     * 
     * @var string $firstname
     */
    private $firstname;
    
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full", "user_details_basic", "admin_add_user"})
     * @Assert\NotBlank(message="user.lastname.notBlank", groups={"admin_add_user", "user_details_basic", "user_details_full"} )
     * @Assert\Length(min=2, max=50, minMessage="user.lastname.minLength", maxMessage="user.lastname.maxLength", groups={"admin_add_user", "user_details_basic", "user_details_full"} )
     * 
     * @var string $lastname
     */
    private $lastname;
    
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"admin_add_user"})
     * @Assert\NotBlank( message="user.email.notBlank", groups={"admin_add_user"} )
     * @Assert\Email( message="user.email.invalid", groups={"admin_add_user"}, checkMX=false, checkHost=false )
     * @var string $email
     */
    private $email;
    
    /**
     * @JMS\Type("string")
     *  @Assert\NotBlank( message="user.password.notBlank", groups={"user_set_password"} )
     *  @Assert\Length( min=8, max=50, minMessage="user.password.minLength", maxMessage="user.password.maxLength", groups={"user_set_password"} )
     *  @Assert\Regex( pattern="/[a-z]/" , message="user.password.noLowerCaseChars", groups={"user_set_password"} )
     *  @Assert\Regex( pattern="/[A-Z]/" , message="user.password.noUpperCaseChars", groups={"user_set_password"} )
     *  @Assert\Regex( pattern="/[0-9]/", message="user.password.noNumber", groups={"user_set_password"} )
     * 
     * @var string $password
     */
    private $password;
    
    /**
     * @JMS\Type("string")
     * @var string $salt
     */
    private $salt;
    
    /**
     * @JMS\Type("boolean")
     * @var boolean $active
     */
    private $active;
    
    /**
     * @JMS\Type("array")
     * @JMS\Accessor(getter="getRole", setter="setRole")
     * @var Role $role
     */
    private $role;
    
    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"admin_add_user"})
     * @Assert\NotBlank( message="user.role.notBlank", groups={"admin_add_user"} )
     * 
     * @var integer
     */
    private $roleId;
    
    /**
     * @JMS\Type("array<AppBundle\Entity\Client>")
     * @var array
     */
    private $clients;
    
    /**
     * @JMS\Type("boolean")
     * @var boolean $emailConfirmed
     */
    private $emailConfirmed;
    
    /**
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * @var \DateTime $registrationDate
     */
    private $registrationDate;
    
    /**
     * @JMS\Type("string")
     * @var string $registrationToken
     */
    private $registrationToken;
    
    /**
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * @var \DateTime $tokenDate
     */
    private $tokenDate;
    
    /**
     * @JMS\Type("string")
     * @var string $gaTrackingId
     */
    private $gaTrackingId;
    
    
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full"})
     * @Assert\NotBlank( message="user.address1.notBlank", groups={"user_details_full"} )
     * 
     * @var string
     */
    private $address1;
    
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full"})
     * 
     * @var string
     */
    private $address2;
    
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full"})
     * 
     * @var string
     */
    private $address3;
    
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full"})
     * @Assert\NotBlank( message="user.addressPostcode.notBlank", groups={"user_details_full"} )   
     * @Assert\Length(min=2, max=50, minMessage="user.addressPostcode.minLength", maxMessage="user.addressPostcode.maxLength", groups={"user_details_full"} )
     * 
     * @var string
     */
    private $addressPostcode;
    
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full"})
     * @Assert\NotBlank( message="user.addressCountry.notBlank", groups={"user_details_full"} )   
     * 
     * @var string
     */
    private $addressCountry;
    
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full"})
     * @Assert\NotBlank( message="user.phoneMain.notBlank", groups={"user_details_full"} )
     * @Assert\Length(min=10, max=25, minMessage="common.genericPhone.minLength", maxMessage="common.genericPhone.maxLength", groups={"user_details_full"} )
     * 
     * @var string
     */
    private $phoneMain;
    
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full"})
     * @Assert\Length(min=10, max=25, minMessage="common.genericPhone.minLength", maxMessage="common.genericPhone.maxLength", groups={"user_details_full"} )
     * 
     * @var string
     */
    private $phoneAlternative;
    
    /**
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * @JMS\Groups({"lastLoggedIn"})
     * @var \DateTime $tokenDate
     */
    private $lastLoggedIn;
    
    /**
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * 
     * @param integer $id
     * @return \AppBundle\Entity\User
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    /**
     * @return string $firstname
     */
    public function getFirstname()
    {
        return $this->firstname;
    }
    
    /**
     * @param string $firstname
     * @return \AppBundle\Entity\User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
        return $this;
    }
    
    /**
     * @return string $lastname
     */
    public function getLastname()
    {
        return $this->lastname;
    }
    
    /**
     * @param string $lastname
     * @return \AppBundle\Entity\User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
        return $this;
    }
    
    /**
     * 
     * @return string $email
     */
    public function getUsername()
    {
        return $this->email;
    }
    
    /**
     * 
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * 
     * @param string $email
     * @return \AppBundle\Entity\User
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }
    
    /**
     * @return string $password
     */
    public function getPassword()
    {
        return $this->password;
    }
    
    /**
     * @param string $password
     * @return \AppBundle\Entity\User
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }
    
    /**
     * 
     * @return null
     */
    public function getSalt()
    {
        return null;
    }
    
    public function setSalt($salt)
    {
       $this->salt = $salt;
       return $this;
    }
    
    /**
     * 
     * @return boolean $active
     */
    public function getActive()
    {
        return $this->active;
    }
    
    /**
     * 
     * @param boolean $active
     * @return \AppBundle\Entity\User
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }
    
    /**
     * 
     * @return array $roles
     */
    public function getRoles()
    {
        return [ $this->role['role'] ];
    }
    
    /**
     * 
     * @param string $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }
    
    public function getRole()
    {
        return $this->role;
    }
    
    public function setClients(array $clients)
    {
        $this->clients = $clients;
    }
    
    public function getClients()
    {
        return $this->clients;
    }
    
    /**
     * 
     * @return boolean
     */
    public function getEmailConfirmed()
    {
        return $this->emailConfirmed;
    }
    
    /**
     * 
     * @param boolean $emailConfirmed
     * @return \AppBundle\Entity\User
     */
    public function setEmailConfirmed($emailConfirmed)
    {
        $this->emailConfirmed = $emailConfirmed;
        return $this;
    }
    
    /**
     * 
     * @return \DateTime $registrationDate
     */
    public function getRegistrationDate()
    {
        return $this->registrationDate;
    }
    
    /**
     * @param \DateTime $registrationDate
     * @return \AppBundle\Entity\User
     */
    public function setRegistrationDate(\DateTime $registrationDate)
    {
        $this->registrationDate = $registrationDate;
        return $this;
    }
    
    /**
     * 
     * @return string $registrationToken
     */
    public function getRegistrationToken()
    {
        return $this->registrationToken;
    }
    
    /**
     * 
     * @param string $registrationToken
     * @return \AppBundle\Entity\User
     */
    public function setRegistrationToken($registrationToken)
    {
        $this->registrationToken = $registrationToken;
        return $this;
    }
    
    /**
     * 
     * @return \DateTime $tokenDate
     */
    public function getTokenDate()
    {
        return $this->tokenDate;
    }
    
    /**
     * 
     * @param \DateTime $tokenDate
     * @return \AppBundle\Entity\User
     */
    public function setTokenDate($tokenDate)
    {
        $this->tokenDate = $tokenDate;
        return $this;
    }
    
    /**
     * @return string $gaTrackingId
     */
    public function getGaTrackingId()
    {
        if(empty($this->gaTrackingId)){
            $this->gaTrackingId = md5($this->id);
        }
        return $this->gaTrackingId;
    }
    
    /**
     * 
     * @param string $gaTrackingId
     * @return \AppBundle\Entity\User
     */
    public function setGaTrackingId($gaTrackingId)
    {
        $this->gaTrackingId = $gaTrackingId;
        return $this;
    }
    
    
    public function eraseCredentials()
    {  
    }
    
    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }
    
    public function isEnabled()
    {
        return $this->active;
    }
    
    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->firstname . ' ' . $this->lastname;
    }
    
    /**
     * @param integer $hoursExpires e.g 48 if the token expires after 48h
     * 
     * @return boolean
     */
    public function isTokenSentInTheLastHours($hoursExpires)
    {
        $expiresSeconds = $hoursExpires * 3600;
        
        $timeStampNow = (new \Datetime())->getTimestamp();
        $timestampToken = $this->getTokenDate()->getTimestamp();
        
        $diffSeconds = $timeStampNow - $timestampToken;
        
        return  $diffSeconds < $expiresSeconds;
    }
    
    public function getRoleId()
    {
        if(empty($this->roleId)){
            $this->roleId = $this->role['id'];
        }
        return $this->roleId;
    }

    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;
        
        return $this;
    }
    
    public function getAddress1()
    {
        return $this->address1;
    }

    public function getAddress2()
    {
        return $this->address2;
    }

    public function getAddress3()
    {
        return $this->address3;
    }

    public function getAddressPostcode()
    {
        return $this->addressPostcode;
    }

    public function getAddressCountry()
    {
        return $this->addressCountry;
    }

    public function getPhoneMain()
    {
        return $this->phoneMain;
    }

    public function getPhoneAlternative()
    {
        return $this->phoneAlternative;
    }

    public function setAddress1($address1)
    {
        $this->address1 = $address1;
    }

    public function setAddress2($address2)
    {
        $this->address2 = $address2;
    }

    public function setAddress3($address3)
    {
        $this->address3 = $address3;
    }

    public function setAddressPostcode($addressPostcode)
    {
        $this->addressPostcode = $addressPostcode;
    }

    public function setAddressCountry($addressCountry)
    {
        $this->addressCountry = $addressCountry;
    }

    public function setPhoneMain($phoneMain)
    {
        $this->phoneMain = $phoneMain;
    }

    public function setPhoneAlternative($phoneAlternative)
    {
        $this->phoneAlternative = $phoneAlternative;
    }
    
    /**
     * @return \DateTime
     */
    public function getLastLoggedIn()
    {
        return $this->lastLoggedIn;
    }

    /**
     * @param \DateTime $lastLoggedIn
     */
    public function setLastLoggedIn(\DateTime $lastLoggedIn = null)
    {
        $this->lastLoggedIn = $lastLoggedIn;
    }
        
    /**
     * @return boolean
     */
    public function hasDetails()
    {
        return $this->getAddress1() || $this->getAddress2() || $this->getAddress3()
               || $this->getAddressCountry() || $this->getAddressPostcode()
               || $this->getPhoneMain() || $this->getPhoneAlternative();
    }
    
    /**
     * @return boolean
     */
    public function hasClients()
    {
        if(!empty($this->clients)){
            return true;
        }
        return false;
    }
    
    public function hasReports()
    {
        $reports = $this->clients[0]->getReports();
        
        if(!empty($reports)){
            return true;
        }
        return false;
    }

    
}