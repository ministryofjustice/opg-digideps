<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\Serializer\Annotation as JMS;

/**
 * Users.
 *
 * @ORM\Table(name="dd_user")
 * @ORM\Entity
 */
class User implements UserInterface
{
    const TOKEN_EXPIRE_HOURS = 48;

    /**
     * @var int
     * @JMS\Type("integer")
     * @JMS\Groups({"audit_log","user"})
     * 
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="user_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @JMS\Groups({ "client"})
     * @JMS\Type("array")
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Client", mappedBy="users", cascade={"persist"})
     */
    private $clients;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({ "audit_log","user"})
     * 
     * @ORM\Column(name="firstname", type="string", length=100, nullable=false)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=100, nullable=true)
     * @JMS\Type("string")
     * @JMS\Groups({ "audit_log","user"})
     */
    private $lastname;

    /**
     * @var string
     * @ORM\Column(name="password", type="string", length=100, nullable=false)
     * @JMS\Exclude
     */
    private $password;

    /**
     * @var string
     * @JMS\Groups({"user"})
     * @JMS\Type("string")
     *
     * @ORM\Column(name="email", type="string", length=60, nullable=false, unique=true)
     */
    private $email;

    /**
     * @var bool
     * @JMS\Type("boolean")
     * @JMS\Groups({"user"})
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
     * @JMS\Groups({"user"})
     *
     * @ORM\Column(name="registration_date", type="datetime", nullable=true)
     */
    private $registrationDate;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"user"})
     * @ORM\Column(name="registration_token", type="string", length=100, nullable=true)
     */
    private $registrationToken;

    /**
     * @var bool
     * @JMS\Type("boolean")
     * @JMS\Groups({"user"})
     * @ORM\Column(name="email_confirmed", type="boolean", nullable=true)
     */
    private $emailConfirmed;

    /**
     * @var \DateTime
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * @JMS\Groups({"user"})
     * 
     * @ORM\Column(name="token_date", type="datetime", nullable=true)
     */
    private $tokenDate;

    /**
     * @var int
     * 
     * @JMS\Groups({"audit_log", "role"})
     * @JMS\Type("AppBundle\Entity\Role")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Role")
     * @ORM\JoinColumn( name="role_id", referencedColumnName="id" )
     */
    private $role;

    /**
     * This id is supplied to GA for UserID tracking. It is an md5 of the user id,
     * does not get stored in the database.
     * 
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"user"})
     */
    private $gaTrackingId;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"user"})
     * @ORM\Column(name="address1", type="string", length=200, nullable=true)
     */
    private $address1;

    /**
     * @var string
     * 
     * @JMS\Type("string")
     * @JMS\Groups({"user"})
     * @ORM\Column(name="address2", type="string", length=200, nullable=true)
     */
    private $address2;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"user"})
     * @ORM\Column(name="address3", type="string", length=200, nullable=true)
     */
    private $address3;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"user"})
     * @ORM\Column(name="address_postcode", type="string", length=10, nullable=true)
     */
    private $addressPostcode;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"user"})
     * @ORM\Column(name="address_country", type="string", length=10, nullable=true)
     */
    private $addressCountry;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"user"})
     * @ORM\Column(name="phone_main", type="string", length=20, nullable=true)
     */
    private $phoneMain;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"user"})
     * @ORM\Column(name="phone_alternative", type="string", length=20, nullable=true)
     */
    private $phoneAlternative;

    /**
     * @var \DateTime
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * @JMS\Groups({"user"})
     * 
     * @ORM\Column(name="last_logged_in", type="datetime", nullable=true)
     */
    private $lastLoggedIn;

    /**
     * @var string
     * 
     * @JMS\Type("string")
     * @ORM\Column(name="deputy_no", type="string", length=100, nullable=true)
     */
    private $deputyNo;

    /**
     * @var bool
     * @JMS\Type("boolean")
     * @JMS\Groups({"user", "user-login"})
     *
     * @ORM\Column(name="odr_enabled", type="boolean", nullable=true, options = { "default": false })
     */
    private $odrEnabled;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->clients = new \Doctrine\Common\Collections\ArrayCollection();
        $this->password = '';
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set firstname.
     *
     * @param string $firstname
     *
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname.
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
        $this->setRegistrationToken('');

        return $this;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = strtolower($email);

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set active.
     *
     * @param bool $active
     *
     * @return User
     */
    public function setActive($active)
    {
        $this->active = (bool) $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set salt.
     *
     * @param string $salt
     *
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Set registrationDate.
     *
     * @param \DateTime $registrationDate
     *
     * @return User
     */
    public function setRegistrationDate($registrationDate)
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    /**
     * Get registrationDate.
     *
     * @return \DateTime
     */
    public function getRegistrationDate()
    {
        return $this->registrationDate;
    }

    /**
     * Set registrationToken.
     *
     * @return User
     */
    public function recreateRegistrationToken()
    {
        $this->setRegistrationToken('digideps'.date('dmY').time(true).rand(17, 999917));
        $this->setTokenDate(new \DateTime());

        return $this;
    }

    /**
     * Set registrationToken.
     *
     * @param string $registrationToken
     *
     * @return User
     */
    public function setRegistrationToken($registrationToken)
    {
        $this->registrationToken = $registrationToken;

        return $this;
    }

    /**
     * Get registrationToken.
     *
     * @return string
     */
    public function getRegistrationToken()
    {
        return $this->registrationToken;
    }

    /**
     * Set emailConfirmed.
     *
     * @param bool $emailConfirmed
     *
     * @return User
     */
    public function setEmailConfirmed($emailConfirmed)
    {
        $this->emailConfirmed = $emailConfirmed;

        return $this;
    }

    /**
     * Get emailConfirmed.
     *
     * @return bool
     */
    public function getEmailConfirmed()
    {
        return $this->emailConfirmed;
    }

    /**
     * Set lastname.
     *
     * @param string $lastname
     *
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname.
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set tokenDate.
     *
     * @param \DateTime $tokenDate
     *
     * @return User
     */
    public function setTokenDate($tokenDate)
    {
        $this->tokenDate = $tokenDate;

        return $this;
    }

    /**
     * Get tokenDate.
     *
     * @return \DateTime
     */
    public function getTokenDate()
    {
        return $this->tokenDate;
    }

    /**
     * Add clients.
     *
     * @param Client $client
     *
     * @return User
     */
    public function addClient(Client $client)
    {
        $client->addUser($this);
        $this->clients[] = $client;

        return $this;
    }

    /**
     * Remove clients.
     *
     * @param Client $clients
     */
    public function removeClient(Client $clients)
    {
        $this->clients->removeElement($clients);
    }

    /**
     * Get clients.
     *
     * @return Client[]
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * Set role.
     *
     * @param Role $role
     *
     * @return User
     */
    public function setRole(Role $role = null)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role.
     *
     * @return Role
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
        return;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRoles()
    {
        return [$this->role->getRole()];
    }

    public function eraseCredentials()
    {
    }

    /**
     * Get gaTrackingId.
     * 
     * @return string $gaTrackingId
     */
    public function getGaTrackingId()
    {
        if (!empty($this->gaTrackingId)) {
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
        return $this->firstname.' '.$this->lastname;
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
    public function getPhoneMain()
    {
        return $this->phoneMain;
    }

    /**
     * @return string
     */
    public function getPhoneAlternative()
    {
        return $this->phoneAlternative;
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
    public function setPhoneMain($phoneMain)
    {
        $this->phoneMain = $phoneMain;
    }

    /**
     * @return string
     */
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

        return $this;
    }

    /**
     * @return string
     */
    public function getDeputyNo()
    {
        return $this->deputyNo;
    }

    /**
     * @param string $deputyNo
     */
    public function setDeputyNo($deputyNo)
    {
        $this->deputyNo = $deputyNo;

        return $this;
    }

    /**
     * Return Id of the client (if it has details)
     *
     * @JMS\VirtualProperty
     * @JMS\Groups({"user-login"})
     * @JMS\Type("integer")
     * @JMS\SerializedName("id_of_client_with_details")
     */
    public function getIdOfClientWithDetails()
    {
        return $this->getFirstClient() && $this->getFirstClient()->hasDetails()
            ? $this->getFirstClient()->getId()
            : null;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"user-login"})
     * @JMS\Type("integer")
     * @JMS\SerializedName("active_report_id")
     */
    public function getActiveReportId()
    {
        $reports = $this->getFirstClient() ? $this->getFirstClient()->getReports() : [];
        foreach ($reports as $report) {
            if (!$report->getSubmitted()) {
                return $report->getId();
            }
        }

        return;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"user-login"})
     * @JMS\Type("integer")
     * @JMS\SerializedName("number_of_reports")
     */
    public function getNumberOfReports()
    {
        return $this->getFirstClient() ? count($this->getFirstClient()->getReports()) : 0;
    }

    /**
     * @return null|Client
     */
    private function getFirstClient()
    {
        $clients = $this->getClients();
        if (count($clients) === 0) {
            return;
        }

        return $clients->first();
    }

    /**
     * @return boolean
     */
    public function getOdrEnabled()
    {
        return $this->odrEnabled;
    }

    /**
     * @param boolean $odrEnabled
     */
    public function setOdrEnabled($odrEnabled)
    {
        $this->odrEnabled = $odrEnabled;

        return $this;
    }

}
