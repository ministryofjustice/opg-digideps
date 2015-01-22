<?php
namespace AppBundle\Entity;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\XmlRoot("user")
 * @JMS\AccessType("public_method")
 */
class User implements AdvancedUserInterface
{   
    /**
     * @JMS\Type("integer")
     * @var integer $id
     */
    private $id;
    
    /**
     * @JMS\Type("string")
     * @var string $firstname
     */
    private $firstname;
    
    /**
     * @JMS\Type("string")
     * @var string $lastname
     */
    private $lastname;
    
    /**
     * @JMS\Type("string")
     * @var string $email
     */
    private $email;
    
    /**
     * @JMS\Type("string")
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
     * @var array $roles
     */
    private $roles;
    
    /**
     * @JMS\Type("boolean")
     * @var boolean $emailConfirmed
     */
    private $emailConfirmed;
    
    /**
     * @JMS\Type("DateTime")
     * @var \DateTime $registrationDate
     */
    private $registrationDate;
    
    /**
     * @JMS\Type("string")
     * @var string $registrationToken
     */
    private $registrationToken;
    
    /**
     * @JMS\Type("DateTime")
     * @var \DateTime $tokenDate
     */
    private $tokenDate;
    
    /**
     * @JMS\Type("string")
     * @var string $gaTrackingId
     */
    private $gaTrackingId;
    
    /**
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
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
        return $this->roles;
    }
    
    /**
     * 
     * @param string $role
     */
    public function addRole($role)
    {
        $this->roles[] = $role; 
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
        return $this->gaTrackingId;
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
}