<?php

namespace App\Entity;

use App\Entity\Traits\LoginInfoTrait;
use App\Validator\Constraints\CommonPassword;
use App\Validator\Constraints\EmailSameDomain;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @codeCoverageIgnore
 */
class User implements UserInterface, DeputyInterface, PasswordAuthenticatedUserInterface
{
    use LoginInfoTrait;

    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    public const ROLE_ADMIN_MANAGER = 'ROLE_ADMIN_MANAGER';

    public const ROLE_LAY_DEPUTY = 'ROLE_LAY_DEPUTY';
    public const ROLE_AD = 'ROLE_AD';

    public const ROLE_PA = 'ROLE_PA';
    public const ROLE_PA_NAMED = 'ROLE_PA_NAMED';
    public const ROLE_PA_ADMIN = 'ROLE_PA_ADMIN';
    public const ROLE_PA_TEAM_MEMBER = 'ROLE_PA_TEAM_MEMBER';

    public const ROLE_PROF = 'ROLE_PROF';
    public const ROLE_PROF_NAMED = 'ROLE_PROF_NAMED';
    public const ROLE_PROF_ADMIN = 'ROLE_PROF_ADMIN';
    public const ROLE_PROF_TEAM_MEMBER = 'ROLE_PROF_TEAM_MEMBER';

    public const ROLE_ORG_NAMED = 'ROLE_ORG_NAMED';
    public const ROLE_ORG_ADMIN = 'ROLE_ORG_ADMIN';

    public const ROLE_ORG = 'ROLE_ORG';

    public const TYPE_LAY = 'LAY';
    public const TYPE_PA = 'PA';
    public const TYPE_PROF = 'PROF';

    public static array $adminRoles = [
        self::ROLE_ADMIN,
        self::ROLE_SUPER_ADMIN,
        self::ROLE_ADMIN_MANAGER,
    ];

    public static array $caseManagerRoles = [
        self::ROLE_ADMIN,
        self::ROLE_ADMIN_MANAGER,
    ];

    /**
     * @JMS\Exclude
     */
    private static $allowedRoles = [
        self::ROLE_ADMIN => 'Admin',
        self::ROLE_ADMIN_MANAGER => 'Admin Manager',
        self::ROLE_SUPER_ADMIN => 'Super admin',
        self::ROLE_LAY_DEPUTY => 'Lay Deputy',
        self::ROLE_AD => 'Assisted Digital',
        // pa
        self::ROLE_PA_NAMED => 'Public Authority (named)',
        self::ROLE_PA_ADMIN => 'Public Authority admin',
        self::ROLE_PA_TEAM_MEMBER => 'Public Authority team member',
        // prof
        self::ROLE_PROF_NAMED => 'Professional Deputy (named)',
        self::ROLE_PROF_ADMIN => 'Professional Deputy admin',
        self::ROLE_PROF_TEAM_MEMBER => 'Professional Deputy team member',
    ];

    public const ACTIVATE_TOKEN_EXPIRE_HOURS = 48;
    public const PASSWORD_TOKEN_EXPIRE_HOURS = 1;

    public const SELF_REGISTER = 'SELF_REGISTER';
    public const ADMIN_INVITE = 'ADMIN_INVITE';
    public const ORG_ADMIN_INVITE = 'ORG_ADMIN_INVITE';
    public const CO_DEPUTY_INVITE = 'CO_DEPUTY_INVITE';
    public const UNKNOWN_REGISTRATION_ROUTE = 'UNKNOWN';

    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"user_details_full", "user_details_basic", "admin_add_user"})
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full", "user_details_basic", "user_details_org", "org_team_add", "admin_add_user", "ad_add_user", "admin_edit_user", "codeputy", "codeputy_invite"})
     * @Assert\NotBlank( message="user.firstname.notBlankOtherUser",
     *     groups={"org_team_add", "user_details_org", "admin_add_user", "ad_add_user", "user_details_basic", "codeputy",
     *     "user_details_full", "verify-codeputy", "admin_edit_user", "codeputy_invite"
     * } )
     * @Assert\Length(min=2, max=50, minMessage="user.firstname.minLength", maxMessage="user.firstname.maxLength",
     * groups={"admin_add_user", "ad_add_user", "user_details_basic", "user_details_full", "user_details_org",
     * "org_team_add", "verify-codeputy", "admin_edit_user", "codeputy_invite"} )
     *
     * @var string
     */
    private $firstname;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full", "user_details_basic", "user_details_org", "org_team_add", "admin_add_user", "ad_add_user", "admin_edit_user", "codeputy", "codeputy_invite"})
     * @Assert\NotBlank(message="user.lastname.notBlankOtherUser",
     *     groups={"admin_add_user","ad_add_user","user_details_basic","user_details_full","org_team_add",
     *     "user_details_org", "verify-codeputy", "admin_edit_user", "codeputy_invite"
     * } )
     * @Assert\Length(min=2, max=50, minMessage="user.lastname.minLength", maxMessage="user.lastname.maxLength", groups={"admin_add_user", "ad_add_user", "user_details_basic", "user_details_full", "user_details_org", "verify-codeputy", "admin_edit_user", "codeputy_invite"} )
     *
     * @var string
     */
    private $lastname;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"admin_add_user", "ad_add_user", "org_team_add", "user_details_full", "user_details_org", "codeputy", "admin_edit_user"})
     * @Assert\NotBlank( message="user.email.notBlank", groups={"admin_add_user", "user_details_full", "user_details_org", "org_team_add", "password_reset", "codeputy_invite", "verify-codeputy", "admin_edit_user", "user_change_email"} )
     * @Assert\Email( message="user.email.invalid", groups={"admin_add_user", "password_reset", "user_details_full", "user_details_org", "org_team_add", "codeputy_invite", "verify-codeputy", "admin_edit_user", "user_change_email"},   )
     * @Assert\Length( max=60, maxMessage="user.email.maxLength", groups={"admin_add_user", "password_reset", "user_details_full", "user_details_org", "org_team_add", "codeputy_invite", "verify-codeputy", "admin_edit_user", "user_change_email"} )
     * @EmailSameDomain( message="user.email.invalidDomain", groups={"email_same_domain"})
     *
     * @var string
     */
    private $email;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank( message="user.password.notBlank", groups={"user_set_password", "user_change_password"} )
     * @Assert\Length( min=14, max=50, minMessage="user.password.minLength", maxMessage="user.password.maxLength", groups={"user_set_password", "user_change_password"} )
     * @Assert\Regex( pattern="/[a-z]/" , message="user.password.noLowerCaseChars", groups={"user_set_password", "user_change_password" } )
     * @Assert\Regex( pattern="/[A-Z]/" , message="user.password.noUpperCaseChars", groups={"user_set_password", "user_change_password" } )
     * @Assert\Regex( pattern="/[0-9]/", message="user.password.noNumber", groups={"user_set_password", "user_change_password"} )
     * @CommonPassword(message="user.password.notCommonPassword", groups={"user_set_password", "user_change_password"})
     *
     * @var string
     */
    private $password;

    /**
     * @JMS\Type("string")
     *
     * @var string|null
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
     * @JMS\Groups({"admin_add_user", "ad_add_user", "org_team_add", "user_details_org"})
     * @Assert\NotBlank( message="user.role.notBlank", groups={"admin_add_user", "ad_add_user"} )
     * @Assert\NotBlank( message="user.role.notBlankPa", groups={"org_team_role_name"} )
     *
     * @var string
     */
    private $roleName;

    /**
     * @JMS\Type("array<App\Entity\Client>")
     *
     * @var Client[]
     */
    private $clients = [];

    /**
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * @JMS\Groups({"user"})
     *
     * @var \DateTime|null
     */
    private $registrationDate;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"registrationToken"})
     *
     * @var string|null
     */
    private $registrationToken;

    /**
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * @JMS\Groups({"registrationToken"})
     *
     * @var \DateTime|null
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
     * @JMS\Groups({"user_details_full", "profile_org"})
     * @Assert\NotBlank( message="user.address1.notBlank", groups={"user_details_full", "verify-codeputy"} )
     * @Assert\Length( max=200, maxMessage="user.address1.maxMessage", groups={"user_details_full", "verify-codeputy"} )
     *
     * @var string
     */
    private $address1;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full", "profile_org"})
     * @Assert\Length( max=200, maxMessage="user.address1.maxMessage", groups={"user_details_full", "profile_org"} )
     *
     * @var string|null
     */
    private $address2;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full", "profile_org"})
     * @Assert\Length( max=200, maxMessage="user.address1.maxMessage", groups={"user_details_full", "profile_org"} )
     *
     * @var string|null
     */
    private $address3;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full", "profile_org"})
     * @Assert\Length( max=200, maxMessage="user.address1.maxMessage", groups={"user_details_full", "profile_org"} )
     *
     * @var string|null
     */
    private $address4;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full", "profile_org"})
     * @Assert\Length( max=200, maxMessage="user.address1.maxMessage", groups={"user_details_full", "profile_org"} )
     *
     * @var string|null
     */
    private $address5;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full", "profile_org", "admin_add_user", "ad_add_user", "admin_edit_user"})
     * @Assert\NotBlank( message="user.addressPostcode.notBlank", groups={"user_details_full", "verify-codeputy", "admin_edit_user"} )
     * @Assert\Length(min=2, max=10, minMessage="user.addressPostcode.minLength", maxMessage="user.addressPostcode.maxLength", groups={"user_details_full", "profile_org", "verify-codeputy", "admin_edit_user", "admin_add_user"} )
     *
     * @var string
     */
    private $addressPostcode;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full", "profile_org"})
     * @Assert\NotBlank( message="user.addressCountry.notBlank", groups={"user_details_full", "verify-codeputy"} )
     *
     * @var string
     */
    private $addressCountry;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full", "user_details_org", "org_team_add"})
     * @Assert\NotBlank( message="user.phoneMain.notBlank", groups={"user_details_full", "verify-codeputy"} )
     * @Assert\Length(min=10, max=20, minMessage="common.genericPhone.minLength", maxMessage="common.genericPhone.maxLength", groups={"user_details_full", "user_details_org", "org_team_add", "verify-codeputy"} )
     *
     * @var string
     */
    private $phoneMain;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full", "profile_org"})
     * @Assert\Length(min=10, max=20, minMessage="common.genericPhone.minLength", maxMessage="common.genericPhone.maxLength", groups={"user_details_full"} )
     *
     * @var string|null
     */
    private $phoneAlternative;

    /**
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * @JMS\Groups({"lastLoggedIn"})
     *
     * @var \DateTime|null
     */
    private $lastLoggedIn;

    /**
     * @var string|null
     *
     * @JMS\Type("string")
     */
    private $deputyNo;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $deputyUid;

    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"admin_add_user", "ad_add_user", "admin_edit_user"})
     *
     * @var bool|null
     */
    private $ndrEnabled;

    /**
     * @var bool|null
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"ad_managed", "ad_add_user"})
     */
    private $adManaged;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_org", "org_team_add"})
     * @Assert\Length(max=150, maxMessage="user.jobTitle.maxMessage", groups={"user_details_org"} )
     *
     * @var string|null
     */
    private $jobTitle;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"agree_terms_use", "update_terms_use"})
     * @Assert\NotBlank( message="user.agreeTermsUse.notBlank", groups={"agree-terms-use"} )
     */
    private $agreeTermsUse;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool|null
     */
    private $isCoDeputy;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $coDeputyClientConfirmed;

    /**
     * @JMS\Type("array<App\Entity\Organisation>")
     * @JMS\Groups({"user_organisations"})
     *
     * @var ArrayCollection
     */
    private $organisations;

    /**
     * @JMS\Type("int")
     * @JMS\Groups({"user"})
     *
     * @var int
     */
    private $numberOfSubmittedReports;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full", "user_details_basic", "admin_add_user"})
     **/
    private ?string $authToken = null;

    /**
     * @JMS\Type("App\Entity\User")
     * @JMS\Groups({"user"})
     *
     * @var User
     */
    private $createdBy;

    /**
     * @JMS\Type("bool")
     * @JMS\Groups({"user"})
     *
     * @var bool
     */
    private $isCaseManager;

    /**
     * @JMS\Type("bool")
     * @JMS\Groups({"user"})
     *
     * @var bool
     */
    private $createdByCaseManager;

    /**
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     *
     * @JMS\Groups({"user"})
     *
     * @var \DateTime|null
     */
    private $preRegisterValidatedDate;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"user"})
     */
    private $registrationRoute;

    public function __construct()
    {
        $this->organisations = new ArrayCollection();
    }

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
     * @return User
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
     * @return User
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
     * @return User
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
        return strtolower($this->email);
    }

    /**
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string $password
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

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
     * @return bool $active
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return User
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    public function setClients(array $clients)
    {
        $this->clients = $clients;

        return $this;
    }

    public function getClients()
    {
        return $this->clients;
    }

    /**
     * @return \DateTime|null $registrationDate
     */
    public function getRegistrationDate()
    {
        return $this->registrationDate;
    }

    /**
     * @return User
     */
    public function setRegistrationDate(?\DateTime $registrationDate = null)
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
     * @return User
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
     * @return User
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
            $this->gaTrackingId = md5(strval($this->id));
        }

        return $this->gaTrackingId;
    }

    /**
     * @param string $gaTrackingId
     *
     * @return User
     */
    public function setGaTrackingId($gaTrackingId)
    {
        $this->gaTrackingId = $gaTrackingId;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsCoDeputy()
    {
        return $this->isCoDeputy;
    }

    /**
     * @param bool $isCoDeputy
     */
    public function setIsCoDeputy($isCoDeputy): self
    {
        $this->isCoDeputy = $isCoDeputy;

        return $this;
    }

    /**
     * @return bool
     */
    public function getCoDeputyClientConfirmed()
    {
        return $this->coDeputyClientConfirmed;
    }

    /**
     * @param bool $isCoDeputyClientConfirmed
     */
    public function setCoDeputyClientConfirmed($isCoDeputyClientConfirmed): self
    {
        $this->coDeputyClientConfirmed = $isCoDeputyClientConfirmed;

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
        return $this->firstname.' '.$this->lastname;
    }

    /**
     * @param int $hoursExpires e.g 48 if the token expires after 48h
     *
     * @return bool
     */
    public function isTokenSentInTheLastHours($hoursExpires)
    {
        $expiresSeconds = $hoursExpires * 3600;

        $timeStampNow = (new \DateTime())->getTimestamp();
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

        return $this;
    }

    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    public function setAddress3($address3)
    {
        $this->address3 = $address3;

        return $this;
    }

    public function setAddressPostcode($addressPostcode)
    {
        $this->addressPostcode = $addressPostcode;

        return $this;
    }

    public function setAddressCountry($addressCountry)
    {
        $this->addressCountry = $addressCountry;

        return $this;
    }

    public function setPhoneMain($phoneMain)
    {
        $this->phoneMain = $phoneMain;

        return $this;
    }

    public function setPhoneAlternative($phoneAlternative)
    {
        $this->phoneAlternative = $phoneAlternative;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastLoggedIn()
    {
        return $this->lastLoggedIn;
    }

    public function setLastLoggedIn(?\DateTime $lastLoggedIn = null)
    {
        $this->lastLoggedIn = $lastLoggedIn;
    }

    /**
     * @return string
     */
    public function getDeputyNo()
    {
        return $this->deputyNo;
    }

    /**
     * @return $this
     */
    public function setDeputyNo($deputyNo)
    {
        $this->deputyNo = $deputyNo;

        return $this;
    }

    public function getDeputyUid(): ?int
    {
        return $this->deputyUid;
    }

    public function setDeputyUid(?int $deputyUid): User
    {
        $this->deputyUid = $deputyUid;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasAddressDetails()
    {
        return !empty($this->getAddress1())
            && !empty($this->getAddressCountry())
            && !empty($this->getAddressPostcode())
            && !empty($this->getPhoneMain());
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
        if (0 === count($this->clients)) {
            return false;
        }

        $reports = $this->clients[0]->getReports();

        if (!empty($reports)) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isNdrEnabled()
    {
        return $this->ndrEnabled;
    }

    /**
     * @param bool $ndrEnabled
     */
    public function setNdrEnabled($ndrEnabled)
    {
        $this->ndrEnabled = $ndrEnabled;
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
     * @param string $jobTitle
     *
     * @return User
     */
    public function setJobTitle($jobTitle)
    {
        $this->jobTitle = $jobTitle;

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
     * @param bool $agreeTermsUse
     *
     * @return User
     */
    public function setAgreeTermsUse($agreeTermsUse)
    {
        $this->agreeTermsUse = $agreeTermsUse;

        return $this;
    }

    /**
     * Is user a Team Member?
     *
     * @return bool
     */
    public function isPaTeamMember()
    {
        return self::ROLE_PA_TEAM_MEMBER === $this->roleName;
    }

    /**
     * Is user a Professional Team Member?
     *
     * @return bool
     */
    public function isProfTeamMember()
    {
        return self::ROLE_PROF_TEAM_MEMBER === $this->roleName;
    }

    /**
     * Is user an organisation Team Member?
     *
     * @return bool
     */
    public function isOrgTeamMember()
    {
        return in_array($this->roleName, [
            self::ROLE_PA_TEAM_MEMBER,
            self::ROLE_PROF_TEAM_MEMBER,
        ]);
    }

    /**
     * Is user a PA Depu ty?
     *
     * @return bool
     */
    public function isDeputyPa()
    {
        return in_array($this->roleName, [
            self::ROLE_PA,
            self::ROLE_PA_NAMED,
            self::ROLE_PA_ADMIN,
            self::ROLE_PA_TEAM_MEMBER,
        ]);
    }

    /**
     * Is user a PA Deputy?
     *
     * @return bool
     */
    public function isDeputyProf()
    {
        return in_array($this->roleName, [
            self::ROLE_PROF,
            self::ROLE_PROF_NAMED,
            self::ROLE_PROF_ADMIN,
            self::ROLE_PROF_TEAM_MEMBER,
        ]);
    }

    /**
     * Is user a PA Administrator?
     *
     * @return bool
     */
    public function isPaAdministrator()
    {
        return in_array($this->roleName, [self::ROLE_PA_ADMIN]);
    }

    /**
     * Is user a PROF Administrator?
     *
     * @return bool
     */
    public function isProfAdministrator()
    {
        return in_array($this->roleName, [self::ROLE_PROF_ADMIN]);
    }

    /**
     * Is user a Organisation Administrator?
     *
     * @return bool
     */
    public function isOrgAdministrator()
    {
        return in_array($this->roleName, [self::ROLE_PA_ADMIN, self::ROLE_PROF_ADMIN]);
    }

    /**
     * Is Organisation Named deputy?
     *
     * @return bool
     */
    public function isOrgNamedDeputy()
    {
        return in_array($this->roleName, [self::ROLE_PA_NAMED, self::ROLE_PROF_NAMED]);
    }

    /**
     * Is user a PA Named Deputy?
     *
     * @return bool
     */
    public function isPaNamedDeputy()
    {
        return in_array($this->roleName, [self::ROLE_PA_NAMED]);
    }

    /**
     * Is user a Prof Named Deputy?
     *
     * @return bool
     */
    public function isProfNamedDeputy()
    {
        return in_array($this->roleName, [self::ROLE_PROF_NAMED]);
    }

    /**
     * Is user a Prof Named or Admin Deputy?
     *
     * @return bool
     */
    public function isProfNamedOrAdmin()
    {
        return in_array($this->roleName, [self::ROLE_PROF_NAMED, self::ROLE_PROF_ADMIN]);
    }

    /**
     * @return bool true if user role is LAY
     */
    public function isLayDeputy()
    {
        return self::ROLE_LAY_DEPUTY === $this->roleName;
    }

    /**
     * Is User a Deputy Either PA or Lay?
     *
     * @return bool true if user role is LAY or PA
     */
    public function isDeputy()
    {
        return $this->isLayDeputy() || $this->isDeputyOrg();
    }

    /**
     * Is user a PA or Prof Deputy?
     *
     * @return bool
     */
    public function isDeputyOrg()
    {
        return in_array($this->roleName, [
            self::ROLE_PA_NAMED,
            self::ROLE_PA_ADMIN,
            self::ROLE_PA_TEAM_MEMBER,
            self::ROLE_PROF_NAMED,
            self::ROLE_PROF_ADMIN,
            self::ROLE_PROF_TEAM_MEMBER,
        ]);
    }

    /**
     * Is user a PA named or a Prof named ?
     *
     * @return bool
     */
    public function hasRoleOrgNamed()
    {
        return in_array($this->roleName, [User::ROLE_PA_NAMED, User::ROLE_PROF_NAMED]);
    }

    /**
     * Is user a PA admin or a Prof admin ?
     *
     * @return bool
     */
    public function hasRoleOrgAdmin()
    {
        return in_array($this->roleName, [User::ROLE_PA_ADMIN, User::ROLE_PROF_ADMIN]);
    }

    /**
     * @return bool
     */
    public function isPaTopRole()
    {
        return self::ROLE_PA === $this->getRoleName();
    }

    /**
     * @return bool
     */
    public function isProfTopRole()
    {
        return self::ROLE_PROF === $this->getRoleName();
    }

    public function isAdmin(): bool
    {
        return self::ROLE_ADMIN === $this->getRoleName();
    }

    public function isSuperAdmin(): bool
    {
        return self::ROLE_SUPER_ADMIN === $this->getRoleName();
    }

    public function isAdminManager(): bool
    {
        return self::ROLE_ADMIN_MANAGER === $this->getRoleName();
    }

    public function hasAdminRole(): bool
    {
        return $this->isAdmin() || $this->isSuperAdmin() || $this->isAdminManager();
    }

    /**
     * Get a generic role output to append to translation keys (ie transkey-PROF).
     *
     * @return string
     */
    public function getRoleForTrans()
    {
        if ($this->isDeputyProf()) {
            return '-PROF';
        } elseif ($this->isDeputyPA()) {
            return '-PA';
        } else {
            return '';
        }
    }

    /**
     * @return array
     */
    public function getAddressNotEmptyParts()
    {
        return array_filter([
            $this->address1,
            $this->address2,
            $this->address3,
            $this->addressPostcode,
            $this->addressCountry,
        ]);
    }

    /**
     * @return ArrayCollection
     */
    public function getOrganisations()
    {
        return $this->organisations;
    }

    /**
     * @param ArrayCollection $organisations
     */
    public function setOrganisations($organisations): self
    {
        $this->organisations = $organisations;

        return $this;
    }

    public function belongsToActiveOrganisation(): bool
    {
        if (empty($this->organisations)) {
            return false;
        }

        foreach ($this->getOrganisations() as $organisation) {
            if ($organisation->isActivated()) {
                return true;
            }
        }

        return false;
    }

    public function getFirstClient(): ?Client
    {
        return count($this->getClients()) > 0 ? $this->getClients()[0] : null;
    }

    public function getNumberOfSubmittedReports(): int
    {
        return $this->numberOfSubmittedReports;
    }

    /**
     * @return User
     */
    public function setNumberOfSubmittedReports(int $numberOfSubmittedReports)
    {
        $this->numberOfSubmittedReports = $numberOfSubmittedReports;

        return $this;
    }

    /**
     * Check if a user registration was before today.
     */
    public function regBeforeToday(User $user): bool
    {
        return $user->getRegistrationDate() < (new \DateTime())->setTime(00, 00, 00);
    }

    public function getAddress4()
    {
        return $this->address4;
    }

    public function setAddress4($address4): User
    {
        $this->address4 = $address4;

        return $this;
    }

    public function getAddress5()
    {
        return $this->address5;
    }

    public function setAddress5($address5): User
    {
        $this->address5 = $address5;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): User
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function isCaseManager(): bool
    {
        return $this->isCaseManager;
    }

    public function setIsCaseManager(bool $isCaseManager): User
    {
        $this->isCaseManager = $isCaseManager;

        return $this;
    }

    public function isCreatedByCaseManager(): bool
    {
        return $this->createdByCaseManager;
    }

    public function setCreatedByCaseManager(bool $createdByCaseManager): User
    {
        $this->createdByCaseManager = $createdByCaseManager;

        return $this;
    }

    public function getUserIdentifier(): ?string
    {
        return $this->email;
    }

    public function getAuthToken(): ?string
    {
        return $this->authToken;
    }

    public function setAuthToken(?string $authToken): User
    {
        $this->authToken = $authToken;

        return $this;
    }

    public function getStandardsLink(): string
    {
        $standardsLink = '';

        if ($this->isLayDeputy()) {
            $standardsLink = 'https://www.gov.uk/government/publications/opg-deputy-standards-guidance-for-lay-deputies/guidance-for-lay-deputies';
        } elseif ($this->isDeputyPa()) {
            $standardsLink = 'https://www.gov.uk/government/publications/opg-deputy-standards-guidance-for-public-authority-deputies/guidance-for-public-authority-deputies';
        } elseif ($this->isDeputyProf()) {
            $standardsLink = 'https://www.gov.uk/government/publications/opg-deputy-standards-guidance-for-professional-deputies/guidance-for-professional-deputies';
        }

        return $standardsLink;
    }

    /**
     * @return User
     */
    public function setPreRegisterValidatedDate(?\DateTime $preRegisterValidatedDate = null)
    {
        $this->preRegisterValidatedDate = $preRegisterValidatedDate;

        return $this;
    }

    /**
     * Get preRegisterValidatedDate.
     *
     * @return ?\DateTime
     */
    public function getPreRegisterValidatedDate(): ?\DateTime
    {
        return $this->preRegisterValidatedDate;
    }

    public function getRegistrationRoute(): string
    {
        return $this->registrationRoute;
    }

    /**
     * @param string $registrationRoute
     */
    public function setRegistrationRoute($registrationRoute): User
    {
        $this->registrationRoute = $registrationRoute;

        return $this;
    }
}
