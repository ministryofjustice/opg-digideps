<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Traits\LoginInfoTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @codeCoverageIgnore
 */
class User implements AdvancedUserInterface
{
    use LoginInfoTrait;

    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_LAY_DEPUTY = 'ROLE_LAY_DEPUTY';
    const ROLE_AD = 'ROLE_AD';
    const ROLE_PA = 'ROLE_PA';
    const ROLE_PA_ADMIN = 'ROLE_PA_ADMIN';
    const ROLE_PA_TEAM_MEMBER = 'ROLE_PA_TEAM_MEMBER';

    /**
     * @JMS\Exclude
     */
    private static $allowedRoles = [
        self::ROLE_ADMIN          => 'OPG Admin',
        self::ROLE_LAY_DEPUTY     => 'Lay Deputy',
        self::ROLE_AD             => 'Assisted Digital',
        self::ROLE_PA             => 'Public Authority',
        self::ROLE_PA_ADMIN       => 'Public Authority admin',
        self::ROLE_PA_TEAM_MEMBER => 'Public Authority team member',
    ];

    const TOKEN_EXPIRE_HOURS = 48;

    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"user_details_full", "user_details_basic", "admin_add_user"})
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full", "user_details_basic", "user_details_pa", "pa_team_add",
     *     "admin_add_user", "ad_add_user"})
     * @Assert\NotBlank( message="user.firstname.notBlank", groups={"admin_add_user", "ad_add_user", "user_details_basic", "user_details_full", "user_details_pa"} )
     * @Assert\NotBlank( message="user.firstname.notBlankOtherUser", groups={"pa_team_add"} )
     * @Assert\Length(min=2, max=50, minMessage="user.firstname.minLength", maxMessage="user.firstname.maxLength",
     *     groups={"admin_add_user", "ad_add_user", "user_details_basic", "user_details_full", "user_details_pa",
     *             "pa_team_add"} )
     *
     * @var string
     */
    private $firstname;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full", "user_details_basic", "user_details_pa", "pa_team_add", "admin_add_user", "ad_add_user"})
     * @Assert\NotBlank(message="user.lastname.notBlank", groups={"admin_add_user", "ad_add_user", "user_details_basic", "user_details_full", "user_details_pa"} )
     * @Assert\NotBlank(message="user.lastname.notBlankOtherUser", groups={"pa_team_add"} )
     * @Assert\Length(min=2, max=50, minMessage="user.lastname.minLength", maxMessage="user.lastname.maxLength", groups={"admin_add_user", "ad_add_user", "user_details_basic", "user_details_full", "user_details_pa"} )
     *
     * @var string
     */
    private $lastname;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"admin_add_user", "ad_add_user", "pa_team_add"})
     * @Assert\NotBlank( message="user.email.notBlank", groups={"admin_add_user", "pa_team_add", "password_reset"} )
     * @Assert\Email( message="user.email.invalid", groups={"admin_add_user", "password_reset", "pa_team_add"}, checkMX=false, checkHost=false )
     * @Assert\Length( max=60, maxMessage="user.email.maxLength", groups={"admin_add_user", "password_reset", "pa_team_add"} )
     *
     * @var string
     */
    private $email;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank( message="user.password.notBlank", groups={"user_set_password"} )
     * @Assert\Length( min=8, max=50, minMessage="user.password.minLength", maxMessage="user.password.maxLength", groups={"user_set_password", "user_change_password"} )
     * @Assert\Regex( pattern="/[a-z]/" , message="user.password.noLowerCaseChars", groups={"user_set_password", "user_change_password" } )
     * @Assert\Regex( pattern="/[A-Z]/" , message="user.password.noUpperCaseChars", groups={"user_set_password", "user_change_password" } )
     * @Assert\Regex( pattern="/[0-9]/", message="user.password.noNumber", groups={"user_set_password", "user_change_password"} )
     *
     * @var string
     */
    private $password;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $salt;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $active;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"admin_add_user", "ad_add_user", "pa_team_add"})
     * @Assert\NotBlank( message="user.role.notBlank", groups={"admin_add_user", "ad_add_user", "pa_team_role_name"} )
     *
     * @var string
     */
    private $roleName;

    /**
     * @JMS\Type("array<AppBundle\Entity\Client>")
     *
     * @var Client[]
     */
    private $clients;

    /**
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     *
     * @var \DateTime
     */
    private $registrationDate;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"registrationToken"})
     *
     * @var string
     */
    private $registrationToken;

    /**
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * @JMS\Groups({"registrationToken"})
     *
     * @var \DateTime
     */
    private $tokenDate;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $gaTrackingId;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full"})
     * @Assert\NotBlank( message="user.address1.notBlank", groups={"user_details_full"} )
     * @Assert\Length( max=200, maxMessage="user.address1.maxMessage", groups={"user_details_full"} )
     *
     * @var string
     */
    private $address1;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full"})
     * @Assert\Length( max=200, maxMessage="user.address1.maxMessage", groups={"user_details_full"} )
     *
     * @var string
     */
    private $address2;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full"})
     * @Assert\Length( max=200, maxMessage="user.address1.maxMessage", groups={"user_details_full"} )
     *
     * @var string
     */
    private $address3;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full"})
     * @Assert\NotBlank( message="user.addressPostcode.notBlank", groups={"user_details_full"} )
     * @Assert\Length(min=2, max=10, minMessage="user.addressPostcode.minLength", maxMessage="user.addressPostcode.maxLength", groups={"user_details_full"} )
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
     * @JMS\Groups({"user_details_full", "user_details_pa", "pa_team_add"})
     * @Assert\NotBlank( message="user.phoneMain.notBlank", groups={"user_details_full", "user_details_pa"} )
     * @Assert\Length(min=10, max=20, minMessage="common.genericPhone.minLength", maxMessage="common.genericPhone.maxLength", groups={"user_details_full", "user_details_pa", "pa_team_add"} )
     *
     * @var string
     */
    private $phoneMain;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full"})
     * @Assert\Length(min=10, max=20, minMessage="common.genericPhone.minLength", maxMessage="common.genericPhone.maxLength", groups={"user_details_full"} )
     *
     * @var string
     */
    private $phoneAlternative;

    /**
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * @JMS\Groups({"lastLoggedIn"})
     *
     * @var \DateTime
     */
    private $lastLoggedIn;

    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"admin_add_user", "ad_add_user"})
     *
     * @var bool
     */
    private $odrEnabled;

    /**
     * @var bool
     * @JMS\Type("boolean")
     * @JMS\Groups({"ad_managed", "ad_add_user"})
     */
    private $adManaged;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_pa", "pa_team_add"})
     * @Assert\NotBlank( message="user.jobTitle.notBlank", groups={"user_details_pa"} )
     * @Assert\Length(max=150, maxMessage="user.jobTitle.maxMessage", groups={"user_details_pa"} )
     *
     * @var string
     */
    private $jobTitle;

    /**
     * PA Team name
     * note: stored as Team entiy in the API. Consider doing the same in the client if Team acquires new fields
     *
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_pa"})
     * @Assert\Length(max=50, maxMessage="user.paTeamName.maxMessage", groups={"user_details_pa"} )
     *
     * @var string
     */
    private $paTeamName;

    /**
     * @var bool
     * @JMS\Type("boolean")
     * @JMS\Groups({"agree_terms_use"})
     *
     * @Assert\NotBlank( message="user.agreeTermsUse.notBlank", groups={"agree-terms-use"} )
     */
    private $agreeTermsUse;

    /**
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
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
     *
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
     *
     * @return \AppBundle\Entity\User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return string $email
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
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
     *
     * @return \AppBundle\Entity\User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt()
    {
        return;
    }

    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * @return bool $active
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return \AppBundle\Entity\User
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
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
     * @return \DateTime $registrationDate
     */
    public function getRegistrationDate()
    {
        return $this->registrationDate;
    }

    /**
     * @param \DateTime $registrationDate
     *
     * @return \AppBundle\Entity\User
     */
    public function setRegistrationDate(\DateTime $registrationDate = null)
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    /**
     * @return string $registrationToken
     */
    public function getRegistrationToken()
    {
        return $this->registrationToken;
    }

    /**
     * @param string $registrationToken
     *
     * @return \AppBundle\Entity\User
     */
    public function setRegistrationToken($registrationToken)
    {
        $this->registrationToken = $registrationToken;

        return $this;
    }

    /**
     * @return \DateTime $tokenDate
     */
    public function getTokenDate()
    {
        return $this->tokenDate;
    }

    /**
     * @param \DateTime $tokenDate
     *
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
        if (empty($this->gaTrackingId)) {
            $this->gaTrackingId = md5($this->id);
        }

        return $this->gaTrackingId;
    }

    /**
     * @param string $gaTrackingId
     *
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
     * @param int $hoursExpires e.g 48 if the token expires after 48h
     *
     * @return bool
     */
    public function isTokenSentInTheLastHours($hoursExpires)
    {
        $expiresSeconds = $hoursExpires * 3600;

        $timeStampNow = (new \Datetime())->getTimestamp();
        $timestampToken = $this->getTokenDate()->getTimestamp();

        $diffSeconds = $timeStampNow - $timestampToken;

        return $diffSeconds < $expiresSeconds;
    }

    public function getRoleName()
    {
        return $this->roleName;
    }

    public function getRoleFullName()
    {
        return self::$allowedRoles[$this->roleName];
    }

    public function setRoleName($roleName)
    {
        $this->roleName = $roleName;

        return $this;
    }

    /**
     * @return array $roles
     */
    public function getRoles()
    {
        return [$this->roleName];
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
     * @return bool
     */
    public function hasDetails()
    {
        if (!empty($this->getAddress1()) && !empty($this->getAddressCountry())
            && !empty($this->getAddressPostcode()) && !empty($this->getPhoneMain())
        ) {
            return true;
        }
    }

    /**
     * @return bool
     */
    public function hasClients()
    {
        if (!empty($this->clients)) {
            return true;
        }

        return false;
    }

    public function hasReports()
    {
        $reports = $this->clients[0]->getReports();

        if (!empty($reports)) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isOdrEnabled()
    {
        return $this->odrEnabled;
    }

    /**
     * @param bool $odrEnabled
     */
    public function setOdrEnabled($odrEnabled)
    {
        $this->odrEnabled = $odrEnabled;
    }

    /**
     * @return bool
     */
    public function isAdManaged()
    {
        return $this->adManaged;
    }

    /**
     * @param bool $adManaged
     */
    public function setAdManaged($adManaged)
    {
        $this->adManaged = $adManaged;
    }

    /**
     * @return string
     */
    public function getJobTitle()
    {
        return $this->jobTitle;
    }

    /**
     * @param  string $jobTitle
     * @return User
     */
    public function setJobTitle($jobTitle)
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaTeamName()
    {
        return $this->paTeamName;
    }

    /**
     * @param  string $paTeamName
     * @return User
     */
    public function setPaTeamName($paTeamName)
    {
        $this->paTeamName = $paTeamName;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAgreeTermsUse()
    {
        return $this->agreeTermsUse;
    }

    /**
     * @param  bool $agreeTermsUse
     * @return User
     */
    public function setAgreeTermsUse($agreeTermsUse)
    {
        $this->agreeTermsUse = $agreeTermsUse;

        return $this;
    }

    public function isDeputyPa()
    {
        return in_array($this->roleName, [self::ROLE_PA, self::ROLE_PA_ADMIN, self::ROLE_PA_TEAM_MEMBER]);
    }

    /**
     * @return bool true if user role is LAY or PA
     */
    public function isDeputy()
    {
        return $this->roleName === self::ROLE_LAY_DEPUTY || $this->isDeputyPa();
    }
}
