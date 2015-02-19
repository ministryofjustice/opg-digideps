<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use JMS\Serializer\Annotation as JMS;

/**
 * Users
 *
 * @ORM\Table(name="dd_user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User implements AdvancedUserInterface
{
    /**
     * @var integer
     * @JMS\Type("integer")
     * 
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="user_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;
    
   
    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Client", mappedBy="users", cascade={"persist"})
     */
    private $clients;

    /**
     * @var string
     * @JMS\Type("string")
     * 
     * @ORM\Column(name="firstname", type="string", length=100, nullable=false)
     */
    private $firstname;
    
    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=100, nullable=true)
     * @JMS\Type("string")
     */
    private $lastname;
    
    /**
     * @var string
     * @ORM\Column(name="password", type="string", length=100, nullable=false)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=60, nullable=false)
     */
    private $email;

    /**
     * @var boolean
     * @JMS\Type("boolean")
     * 
     * @ORM\Column(name="active", type="boolean", nullable=true, options = { "default": false })
     */
    private $active;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=100, nullable=true)
     */
    private $salt;

    /**
     * @var \DateTime
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     *
     * @ORM\Column(name="registration_date", type="datetime", nullable=true)
     */
    private $registrationDate;

    /**
     * @var string
     *
     * @ORM\Column(name="registration_token", type="string", length=100, nullable=true)
     */
    private $registrationToken;

    /**
     * @var boolean
     *
     * @ORM\Column(name="email_confirmed", type="boolean", nullable=true)
     */
    private $emailConfirmed;


    /**
     * @var \DateTime
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * 
     * @ORM\Column(name="token_date", type="datetime", nullable=true)
     */
    private $tokenDate;

    /**
     * @var integer
     * 
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Role", inversedBy="user" )
     * @ORM\JoinColumn( name="role_id", referencedColumnName="id" )
     */
    private $role;
    
    /**
     * This id is supplied to GA for UserID tracking. It is an md5 of the user id,
     * does not get stored in the database
     * 
     * @var string
     */
    private $gaTrackingId;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="address1", type="string", length=200, nullable=true)
     */
    private $address1;
    
    /**
     * @var string
     *
     * @ORM\Column(name="address2", type="string", length=200, nullable=true)
     */
    private $address2;
    
    /**
     * @var string
     *
     * @ORM\Column(name="address3", type="string", length=200, nullable=true)
     */
    private $address3;

    /**
     * @var string
     *
     * @ORM\Column(name="address_postcode", type="string", length=10, nullable=true)
     */
    private $addressPostcode;
    
    /**
     * @var string
     *
     * @ORM\Column(name="address_country", type="string", length=10, nullable=true)
     */
    private $addressCountry;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_work", type="string", length=20, nullable=true)
     */
    private $phoneWork;
    
     /**
     * @var string
     *
     * @ORM\Column(name="phone_home", type="string", length=20, nullable=true)
     */
    private $phoneHome;
    
    /**
     * @var string
     *
     * @ORM\Column(name="phone_mobile", type="string", length=20, nullable=true)
     */
    private $phoneMobile;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->clients = new \Doctrine\Common\Collections\ArrayCollection();
        
        $this->registrationToken = sha1('sdfs'.rand(1, 100) . time().date('dmY'));
        $this->tokenDate = new \DateTime();
        $this->password = '';
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
        $this->setRegistrationToken('');
        
        return $this;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return User
     */
    public function setActive($active)
    {
        $this->active = (bool)$active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Set registrationDate
     *
     * @param \DateTime $registrationDate
     * @return User
     */
    public function setRegistrationDate($registrationDate)
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    /**
     * Get registrationDate
     *
     * @return \DateTime 
     */
    public function getRegistrationDate()
    {
        return $this->registrationDate;
    }

    /**
     * Set registrationToken
     *
     * @param string $registrationToken
     * @return User
     */
    public function setRegistrationToken($registrationToken)
    {
        $this->registrationToken = $registrationToken;
        $this->setTokenDate(new \DateTime);
        
        return $this;
    }

    /**
     * Get registrationToken
     *
     * @return string 
     */
    public function getRegistrationToken()
    {
        return $this->registrationToken;
    }

    /**
     * Set emailConfirmed
     *
     * @param boolean $emailConfirmed
     * @return User
     */
    public function setEmailConfirmed($emailConfirmed)
    {
        $this->emailConfirmed = $emailConfirmed;

        return $this;
    }

    /**
     * Get emailConfirmed
     *
     * @return boolean 
     */
    public function getEmailConfirmed()
    {
        return $this->emailConfirmed;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string 
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set tokenDate
     *
     * @param \DateTime $tokenDate
     * @return User
     */
    public function setTokenDate($tokenDate)
    {
        $this->tokenDate = $tokenDate;

        return $this;
    }

    /**
     * Get tokenDate
     *
     * @return \DateTime 
     */
    public function getTokenDate()
    {
        return $this->tokenDate;
    }

    /**
     * Add clients
     *
     * @param \AppBundle\Entity\Client $clients
     * @return User
     */
    public function addClient(\AppBundle\Entity\Client $clients)
    {
        $this->clients[] = $clients;

        return $this;
    }

    /**
     * Remove clients
     *
     * @param \AppBundle\Entity\Client $clients
     */
    public function removeClient(\AppBundle\Entity\Client $clients)
    {
        $this->clients->removeElement($clients);
    }

    /**
     * Get clients
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * Set role
     *
     * @param \AppBundle\Entity\Role $role
     * @return User
     */
    public function setRole(\AppBundle\Entity\Role $role = null)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return \AppBundle\Entity\Role 
     */
    public function getRole()
    {
        return $this->role;
    }
    
    public function getUsername() 
    {
        return $this->email;
    }
    
    public function getSalt() 
    {
        //return $this->salt;
        return null;
    }
    
    public function getPassword() 
    {
        return $this->password;
    }
    
    public function getRoles() 
    {
        $roles = [ $this->role->getRole()];
        
        return $roles;
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
     * Get gaTrackingId
     * 
     * @return string $gaTrackingId
     */
    public function getGaTrackingId()
    {
        if(!empty($this->gaTrackingId)){
            return $this->gaTrackingId;
        }
        $this->gaTrackingId = md5($this->id);
       
        return $this->gaTrackingId;
    }
    
    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->firstname . ' ' . $this->lastname;
    }
    
    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @return string
     */
    public function getAddress3()
    {
        return $this->address3;
    }

    /**
     * @return string
     */
    public function getAddressPostcode()
    {
        return $this->addressPostcode;
    }
    
    /**
     * @return string
     */
    public function getAddressCountry()
    {
        return $this->addressCountry;
    }

    /**
     * @return string
     */
    public function getPhoneWork()
    {
        return $this->phoneWork;
    }

    /**
     * @return string
     */
    public function getPhoneHome()
    {
        return $this->phoneHome;
    }

    /**
     * @return string
     */
    public function getPhoneMobile()
    {
        return $this->phoneMobile;
    }

    /**
     * @return string
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;
    }

    /**
     * @return string
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;
    }

    /**
     * @return string
     */
    public function setAddress3($address3)
    {
        $this->address3 = $address3;
    }

    /**
     * @return string
     */
    public function setAddressPostcode($addressPostcode)
    {
        $this->addressPostcode = $addressPostcode;
    }
    
    /**
     * @return string
     */
    public function setAddressCountry($addressCountry)
    {
        $this->addressCountry = $addressCountry;
    }

    /**
     * @return string
     */
    public function setPhoneWork($phoneWork)
    {
        $this->phoneWork = $phoneWork;
    }

    /**
     * @return string
     */
    public function setPhoneHome($phoneHome)
    {
        $this->phoneHome = $phoneHome;
    }

    /**
     * @return string
     */
    public function setPhoneMobile($phoneMobile)
    {
        $this->phoneMobile = $phoneMobile;
    }

}
