<?php

namespace App\Entity;

use App\Entity\Report\Report;
use App\Entity\Traits\AddressTrait;
use App\Entity\Traits\CreateUpdateTimestamps;
use App\Entity\UserResearch\UserResearchResponse;
use App\Model\Hydrator;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Users.
 *
 * @ORM\Table(name="dd_user", indexes={
 *
 *     @ORM\Index(name="deputy_no_idx", columns={"deputy_no"}),
 *     @ORM\Index(name="created_by_idx", columns={"created_by_id"})
 * })
 *
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 *
 * @ORM\HasLifecycleCallbacks()
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use CreateUpdateTimestamps;
    use AddressTrait;

    public const ACTIVATE_TOKEN_EXPIRE_HOURS = 48;

    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_ADMIN_MANAGER = 'ROLE_ADMIN_MANAGER';
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    public const ROLE_DEPUTY = 'ROLE_DEPUTY';
    public const ROLE_LAY_DEPUTY = 'ROLE_LAY_DEPUTY';
    public const ROLE_AD = 'ROLE_AD';

    public const ROLE_ORG_NAMED = 'ROLE_ORG_NAMED';
    public const ROLE_ORG_ADMIN = 'ROLE_ORG_ADMIN';
    public const ROLE_ORG_TEAM_MEMBER = 'ROLE_ORG_TEAM_MEMBER';

    public const ROLE_PA = 'ROLE_PA';
    public const ROLE_PA_NAMED = 'ROLE_PA_NAMED';
    public const ROLE_PA_ADMIN = 'ROLE_PA_ADMIN';
    public const ROLE_PA_TEAM_MEMBER = 'ROLE_PA_TEAM_MEMBER';

    public const ROLE_PROF = 'ROLE_PROF';
    public const ROLE_PROF_NAMED = 'ROLE_PROF_NAMED';
    public const ROLE_PROF_ADMIN = 'ROLE_PROF_ADMIN';
    public const ROLE_PROF_TEAM_MEMBER = 'ROLE_PROF_TEAM_MEMBER';

    public const TYPE_LAY = 'LAY';
    public const TYPE_PA = 'PA';
    public const TYPE_PROF = 'PROF';

    public static $adminRoles = [
        self::ROLE_ADMIN,
        self::ROLE_SUPER_ADMIN,
        self::ROLE_ADMIN_MANAGER,
    ];

    public static array $caseManagerRoles = [
        self::ROLE_ADMIN,
        self::ROLE_ADMIN_MANAGER,
    ];

    public static $orgRoles = [
        self::ROLE_PA,
        self::ROLE_PA_NAMED,
        self::ROLE_PA_ADMIN,
        self::ROLE_PA_TEAM_MEMBER,
        self::ROLE_PROF,
        self::ROLE_PROF_NAMED,
        self::ROLE_PROF_ADMIN,
        self::ROLE_PROF_TEAM_MEMBER,
        self::ROLE_ORG_NAMED,
        self::ROLE_ORG_ADMIN,
        self::ROLE_ORG_TEAM_MEMBER,
    ];

    public static $depTypeIdToRealm = [
        // PA
        23 => PreRegistration::REALM_PA,
        // PROFESSIONAL
        21 => PreRegistration::REALM_PROF,
        26 => PreRegistration::REALM_PROF,
        63 => PreRegistration::REALM_PROF,
        22 => PreRegistration::REALM_PROF,
        24 => PreRegistration::REALM_PROF,
        25 => PreRegistration::REALM_PROF,
        27 => PreRegistration::REALM_PROF,
        29 => PreRegistration::REALM_PROF,
        50 => PreRegistration::REALM_PROF,
    ];

    public const SELF_REGISTER = 'SELF_REGISTER';
    public const ADMIN_INVITE = 'ADMIN_INVITE';
    public const ORG_ADMIN_INVITE = 'ORG_ADMIN_INVITE';
    public const CO_DEPUTY_INVITE = 'CO_DEPUTY_INVITE';
    public const UNKNOWN_REGISTRATION_ROUTE = 'UNKNOWN';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\SequenceGenerator(sequenceName="user_id_seq", allocationSize=1, initialValue=1)
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['user', 'report-submitted-by', 'user-id', 'user-list'])]
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Client", mappedBy="users", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    #[JMS\Groups(['user-clients'])]
    #[JMS\Type('ArrayCollection<App\Entity\Client>')]
    private $clients;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Organisation", mappedBy="users", fetch="EXTRA_LAZY")
     *
     * @var ArrayCollection
     */
    #[JMS\Type('ArrayCollection<App\Entity\Organisation>')]
    #[JMS\Groups(['user-organisations'])]
    #[JMS\Accessor(getter: 'getOrganisations')]
    private $organisations;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=100, nullable=false)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['user', 'report-submitted-by', 'user-name', 'user-list'])]
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=100, nullable=false)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['user', 'report-submitted-by', 'user-name', 'user-list'])]
    private $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=100, nullable=false)
     */
    #[JMS\Groups(['user-login'])]
    #[JMS\Exclude]
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=60, nullable=false, unique=true)
     */
    #[JMS\Groups(['user', 'report-submitted-by', 'user-email', 'user-list'])]
    #[JMS\Type('string')]
    private $email;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=true, options = { "default": false })
     */
    #[JMS\Type('boolean')]
    #[JMS\Groups(['user', 'user-list'])]
    private $active;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=100, nullable=true)
     */
    private $salt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="registration_date", type="datetime", nullable=true)
     */
    #[JMS\Type("DateTime<'Y-m-d H:i:s'>")]
    #[JMS\Groups(['user'])]
    private $registrationDate;

    /**
     * @var string
     *
     * @ORM\Column(name="registration_token", type="string", length=100, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['user'])]
    private $registrationToken;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="token_date", type="datetime", nullable=true)
     */
    #[JMS\Type("DateTime<'Y-m-d H:i:s'>")]
    #[JMS\Groups(['user'])]
    private $tokenDate;

    /**
     * @var string ROLE_
     *             see roles in Role class
     *
     * @ORM\Column(name="role_name", type="string", length=50, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['user', 'report-submitted-by', 'user-rolename', 'user-list', 'team-users'])]
    private $roleName;

    /**
     * This id is supplied to GA for UserID tracking. It is an md5 of the user id,
     * does not get stored in the database.
     *
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['user'])]
    private $gaTrackingId;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_main", type="string", length=20, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['user', 'report-submitted-by', 'user-list', 'user-phone-main'])]
    private $phoneMain;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_alternative", type="string", length=20, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['user', 'report-submitted-by'])]
    private $phoneAlternative;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_logged_in", type="datetime", nullable=true)
     */
    #[JMS\Type("DateTime<'Y-m-d H:i:s'>")]
    #[JMS\Groups(['user'])]
    private $lastLoggedIn;

    /**
     * @var string
     *
     * @ORM\Column(name="deputy_no", type="string", length=100, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['user'])]
    private $deputyNo;

    /**
     * @var int
     *
     * @ORM\Column(name="deputy_uid", type="bigint", nullable=true)
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['user'])]
    private $deputyUid;

    /**
     * @var bool
     *
     * @ORM\Column(name="odr_enabled", type="boolean", nullable=true, options = { "default": false })
     */
    #[JMS\Type('boolean')]
    #[JMS\Groups(['user', 'user-login'])]
    private $ndrEnabled;

    /**
     * @var bool
     *
     * @ORM\Column(name="ad_managed", type="boolean", nullable=true, options = { "default": false })
     */
    #[JMS\Type('boolean')]
    #[JMS\Groups(['user'])]
    private $adManaged;

    /**
     * @ORM\Column(name="job_title", type="string", length=150, nullable=true)
     *
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['user', 'user-list'])]
    private $jobTitle;

    /**
     * @var bool
     *
     * @ORM\Column(name="agree_terms_use", type="boolean", nullable=true, options = { "default": false })
     */
    #[JMS\Type('boolean')]
    #[JMS\Groups(['user'])]
    private $agreeTermsUse;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="agree_terms_use_date", type="datetime", nullable=true)
     */
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['user'])]
    private $agreeTermsUseDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="codeputy_client_confirmed", type="boolean", nullable=false, options = { "default": false })
     */
    #[JMS\Type('boolean')]
    #[JMS\Groups(['user'])]
    private $coDeputyClientConfirmed;

    /**
     * @var UserResearchResponse|null
     *
     * @ORM\OneToMany(targetEntity="App\Entity\UserResearch\UserResearchResponse", mappedBy="user", cascade={"persist"})
     */
    #[JMS\Type('App\Entity\UserResearch\UserResearchResponse')]
    #[JMS\Groups(['user', 'satisfaction', 'user-research'])]
    private $userResearchResponse;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="user")
     *
     * @ORM\JoinColumn(name="created_by_id", referencedColumnName="id", onDelete="SET NULL")
     */
    #[JMS\Type('App\Entity\User')]
    #[JMS\Groups(['user', 'created-by'])]
    #[JMS\MaxDepth(3)]
    private $createdBy;

    /**
     * @var bool
     *
     * @ORM\Column(name="deletion_protection", type="boolean", nullable=true, options = { "default": null })
     */
    #[JMS\Type('boolean')]
    #[JMS\Groups(['user'])]
    private $deletionProtection;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Deputy", mappedBy="user", cascade={"persist"})
     */
    #[JMS\Type('App\Entity\Deputy')]
    private ?Deputy $deputy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="pre_register_validated", type="datetime", nullable=true)
     */
    #[JMS\Type("DateTime<'Y-m-d H:i:s'>")]
    #[JMS\Groups(['user'])]
    private $preRegisterValidatedDate;

    /**
     * @var string
     *
     * @ORM\Column(name="registration_route", type="string", length=30, nullable=false, options = { "default": "UNKNOWN" })
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['user'])]
    private $registrationRoute = self::UNKNOWN_REGISTRATION_ROUTE;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_primary", type="boolean", nullable=false, options = { "default": false })
     */
    #[JMS\Type('boolean')]
    #[JMS\Groups(['user'])]
    private $isPrimary = false;

    public function __construct($coDeputyClientConfirmed = false)
    {
        $this->clients = new ArrayCollection();
        $this->password = '';
        $this->organisations = new ArrayCollection();
        $this->setCoDeputyClientConfirmed($coDeputyClientConfirmed);
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
     * @return User
     */
    public function setId(?int $id)
    {
        $this->id = $id;

        return $this;
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
     *
     * @throws \Exception
     */
    public function recreateRegistrationToken()
    {
        $userIdWithLeadingZeros = sprintf('%08d', $this->getId());
        $token = bin2hex(random_bytes(16)).$userIdWithLeadingZeros;

        $this->setRegistrationToken($token);
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
     * @return User
     */
    public function addClient(Client $client)
    {
        $client->addUser($this);
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
        }

        return $this;
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
     * @return Organisation[]
     */
    public function getOrganisations()
    {
        return $this->organisations->filter(function ($organisation) {
            return $organisation->isActivated();
        });
    }

    /**
     * @return User
     */
    public function addOrganisation(Organisation $organisation)
    {
        if (!$this->organisations->contains($organisation)) {
            $this->organisations->add($organisation);
        }

        return $this;
    }

    public function getOrganisationIds(): array
    {
        $organisationIds = [];
        foreach ($this->getOrganisations() as $organisation) {
            $organisationIds[] = $organisation->getId();
        }

        return $organisationIds;
    }

    public function getRoleName()
    {
        return $this->roleName;
    }

    /**
     * @param string $roleName ROLE_.*
     *
     * @return User
     */
    public function setRoleName($roleName)
    {
        $this->roleName = $roleName;

        return $this;
    }

    public function getSalt(): void
    {
        return;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        return [$this->roleName];
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
     * @return $this
     */
    public function setPhoneMain($phoneMain)
    {
        $this->phoneMain = $phoneMain;

        return $this;
    }

    /**
     * @return $this
     */
    public function setPhoneAlternative($phoneAlternative)
    {
        $this->phoneAlternative = $phoneAlternative;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastLoggedIn()
    {
        return $this->lastLoggedIn;
    }

    public function setLastLoggedIn(?\DateTime $lastLoggedIn = null)
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
     * convert 7 into 00000007.
     *
     * @return string
     */
    public static function padDeputyNumber($deputyNo)
    {
        return str_pad($deputyNo, 8, '0', STR_PAD_LEFT);
    }

    /**
     * @param string $deputyNo
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
     * Return Id of the client (if it has details).
     */
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('id_of_client_with_details')]
    #[JMS\Groups(['user'])]
    #[JMS\Type('integer')]
    public function getIdOfClientWithDetails()
    {
        return $this->getFirstClient() && $this->getFirstClient()->hasDetails()
            ? $this->getFirstClient()->getId()
            : null;
    }

    #[JMS\VirtualProperty]
    #[JMS\Groups(['user-login'])]
    #[JMS\Type('integer')]
    #[JMS\SerializedName('active_report_id')]
    public function getActiveReportId()
    {
        $client = $this->getFirstClient() ? $this->getFirstClient() : null;
        if (!$client) {
            return null;
        }

        if (1 === $client->getUnsubmittedReports()->count()) {
            return $client->getUnsubmittedReports()->first()->getId();
        }

        return null;
    }

    #[JMS\VirtualProperty]
    #[JMS\Groups(['user'])]
    #[JMS\Type('integer')]
    #[JMS\SerializedName('number_of_reports')]
    public function getNumberOfReports()
    {
        return $this->getFirstClient() ? count($this->getFirstClient()->getReports()) : 0;
    }

    #[JMS\VirtualProperty]
    #[JMS\Groups(['user'])]
    #[JMS\Type('integer')]
    #[JMS\SerializedName('number_of_submitted_reports')]
    public function getNumberOfSubmittedReports()
    {
        if (!$this->getFirstClient()) {
            return 0;
        }

        $isSubmittedClosure = function (Report $report) {
            return !is_null($report->getSubmitDate());
        };

        $submittedReports = array_filter(
            $this->getFirstClient()->getReports()->toArray(),
            $isSubmittedClosure
        );

        return count($submittedReports);
    }

    /**
     * @return Client|null
     */
    public function getFirstClient()
    {
        $clients = $this->getClients();
        if (0 === count($clients)) {
            return null;
        }

        return $clients->first();
    }

    /**
     * @return bool
     */
    public function getNdrEnabled()
    {
        return $this->ndrEnabled;
    }

    /**
     * @param bool $ndrEnabled
     */
    public function setNdrEnabled($ndrEnabled): User
    {
        $this->ndrEnabled = $ndrEnabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAdManaged()
    {
        return $this->adManaged;
    }

    public function setAdManaged(?bool $adManaged)
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
     * @return User
     */
    public function setJobTitle(?string $jobTitle)
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

    public function setAgreeTermsUse(?bool $agreeTermsUse): User
    {
        $this->agreeTermsUse = $agreeTermsUse;

        if ($agreeTermsUse) {
            $this->agreeTermsUseDate = new \DateTime('now');
        }

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getAgreeTermsUseDate()
    {
        return $this->agreeTermsUseDate;
    }

    /**
     * @return bool
     */
    public function getCoDeputyClientConfirmed()
    {
        return $this->coDeputyClientConfirmed;
    }

    /**
     * @param bool $coDeputyClientConfirmed
     */
    public function setCoDeputyClientConfirmed($coDeputyClientConfirmed = false): User
    {
        $this->coDeputyClientConfirmed = $coDeputyClientConfirmed;

        return $this;
    }

    /**
     * Return true if the client has other users.
     *
     * @return bool
     */
    #[JMS\VirtualProperty]
    #[JMS\Type('boolean')]
    #[JMS\SerializedName('is_co_deputy')]
    #[JMS\Groups(['user'])]
    public function isCoDeputy()
    {
        $isCoDeputy = false;
        if ($this->isLayDeputy()) {
            $client = $this->getFirstClient();
            if (!empty($client)) {
                $isCoDeputy = count($client->getUsers()) > 1;
            }
        }

        return $isCoDeputy;
    }

    /**
     * Is a PA (any role)?
     *
     * @return bool
     */
    public function isPaDeputy()
    {
        return $this->isPaNamedDeputy() || $this->isPaAdministrator() || $this->isPaTeamMember() || $this->isPaTopRole();
    }

    /**
     * Is a Professional Deputy (any role)?
     *
     * @return bool
     */
    public function isProfDeputy()
    {
        return $this->isProfNamedDeputy() || $this->isProfAdministrator() || $this->isProfTeamMember() || $this->isProfTopRole();
    }

    /**
     * Is Organisation Named deputy?
     *
     * @return bool
     */
    public function isOrgNamedDeputy()
    {
        return $this->isPaNamedDeputy() || $this->isProfNamedDeputy();
    }

    /**
     * Is PA Named deputy?
     *
     * @return bool
     */
    public function isPaNamedDeputy()
    {
        return self::ROLE_PA_NAMED === $this->getRoleName();
    }

    /**
     * Is PA Named deputy?
     *
     * @return bool
     */
    public function isProfNamedDeputy()
    {
        return self::ROLE_PROF_NAMED === $this->getRoleName();
    }

    /**
     * @return bool
     */
    public function isLayDeputy()
    {
        return self::ROLE_LAY_DEPUTY === $this->getRoleName();
    }

    /**
     * Is PA Administrator?
     *
     * @return bool
     */
    public function isPaAdministrator()
    {
        return in_array($this->roleName, [self::ROLE_PA_ADMIN]);
    }

    /**
     * Is user a Professional Administrator?
     *
     * @return bool
     */
    public function isProfAdministrator()
    {
        return in_array($this->roleName, [self::ROLE_PROF_ADMIN]);
    }

    /**
     * Is Organisation Administrator?
     *
     * @return bool
     */
    public function isOrgAdministrator()
    {
        return $this->isPaAdministrator() || $this->isProfAdministrator();
    }

    /**
     * Is PA Team member?
     *
     * @return bool
     */
    public function isPaTeamMember()
    {
        return self::ROLE_PA_TEAM_MEMBER === $this->getRoleName();
    }

    /**
     * Is Professional Team member?
     *
     * @return bool
     */
    public function isProfTeamMember()
    {
        return self::ROLE_PROF_TEAM_MEMBER === $this->getRoleName();
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

    /**
     * @return bool
     */
    public function isOrgNamedOrAdmin()
    {
        return $this->isOrgNamedDeputy() || $this->isOrgAdministrator();
    }

    /**
     * Is user an organisation Team Member?
     *
     * @return bool
     */
    public function isOrgTeamMember()
    {
        return $this->isPaTeamMember() || $this->isProfTeamMember();
    }

    /**
     * Is user an Organisation deputy? Any role. PA or Org.
     *
     * @return bool
     */
    public function isDeputyOrg()
    {
        return $this->isOrgNamedDeputy() || $this->isOrgAdministrator() || $this->isOrgTeamMember();
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
     * Set role to team member.
     */
    public function setDefaultRoleIfEmpty()
    {
        if (empty($this->getRoleName())) {
            if ($this->isProfDeputy()) {
                $this->setRoleName(User::ROLE_PROF_TEAM_MEMBER);
            } elseif ($this->isPaDeputy()) {
                $this->setRoleName(User::ROLE_PA_TEAM_MEMBER);
            }
        }
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

    public function isCoDeputyWith(User $coDeputy): bool
    {
        foreach ($coDeputy->getClients() as $coDeputyClient) {
            foreach ($this->getClients() as $userClient) {
                if ($coDeputyClient === $userClient) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getUserResearchResponse(): ?UserResearchResponse
    {
        return $this->userResearchResponse;
    }

    public function setUserResearchResponse(?UserResearchResponse $userResearchResponse): User
    {
        $this->userResearchResponse = $userResearchResponse;

        return $this;
    }

    public function getDeputy(): ?Deputy
    {
        return $this->deputy;
    }

    public function setDeputy(?Deputy $deputy): User
    {
        $this->deputy = $deputy;

        return $this;
    }

    /**
     * Check if a user registration was before today.
     */
    public function regBeforeToday(User $user): bool
    {
        return $user->getRegistrationDate() < (new \DateTime())->setTime(00, 00, 00);
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

    #[JMS\VirtualProperty]
    #[JMS\SerializedName('is_case_manager')]
    #[JMS\Groups(['user'])]
    #[JMS\Type('bool')]
    public function isCaseManager(): bool
    {
        return in_array($this->getRoleName(), $this::$caseManagerRoles);
    }

    #[JMS\VirtualProperty]
    #[JMS\SerializedName('created_by_case_manager')]
    #[JMS\Groups(['user'])]
    #[JMS\Type('bool')]
    public function createdByCaseManager(): bool
    {
        return $this->getCreatedBy() && $this->getCreatedBy()->isCaseManager();
    }

    /**
     * The public representation of the user (e.g. a username, an email address, etc.).
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * Set preRegisterValidatedDate.
     *
     * @param \DateTime $preRegisterValidatedDate
     */
    public function setPreRegisterValidatedDate($preRegisterValidatedDate): User
    {
        $this->preRegisterValidatedDate = $preRegisterValidatedDate;

        return $this;
    }

    /**
     * Get preRegisterValidatedDate.
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

    /**
     * Set primary.
     *
     * @param bool $primary
     */
    public function setIsPrimary($primary): User
    {
        $this->isPrimary = $primary;

        return $this;
    }

    /**
     * Get primary.
     */
    public function getIsPrimary(): bool
    {
        return $this->isPrimary;
    }

    /**
     * Populate user fields from an array of data.
     *
     * If $data is null, the object is returned unchanged.
     */
    public function populate(?array $data): static
    {
        if (is_null($data)) {
            return $this;
        }

        $keySetters = [
            'firstname' => 'setFirstname',
            'lastname' => 'setLastname',
            'email' => 'setEmail',
            'address1' => 'setAddress1',
            'address2' => 'setAddress2',
            'address3' => 'setAddress3',
            'address_postcode' => 'setAddressPostcode',
            'address_country' => 'setAddressCountry',
            'phone_alternative' => 'setPhoneAlternative',
            'phone_main' => 'setPhoneMain',
            'ndr_enabled' => 'setNdrEnabled',
            'ad_managed' => 'setAdManaged',
            'role_name' => 'setRoleName',
            'job_title' => 'setJobTitle',
            'co_deputy_client_confirmed' => 'setCoDeputyClientConfirmed',
        ];

        Hydrator::hydrateEntityWithArrayData($this, $data, $keySetters);

        if (array_key_exists('deputy_no', $data) && !empty($data['deputy_no'])) {
            $this->setDeputyNo($data['deputy_no']);
        }

        if (array_key_exists('deputy_uid', $data) && !empty($data['deputy_uid'])) {
            $this->setDeputyUid($data['deputy_uid']);
        }

        if (array_key_exists('last_logged_in', $data)) {
            $this->setLastLoggedIn(new \DateTime($data['last_logged_in']));
        }

        if (!empty($data['registration_token'])) {
            $this->setRegistrationToken($data['registration_token']);
        }

        if (!empty($data['token_date'])) { // important, keep this after "setRegistrationToken" otherwise date will be reset
            $this->setTokenDate(new \DateTime($data['token_date']));
        }

        if (!empty($data['role_name'])) {
            $roleToSet = $data['role_name'];
            $this->setRoleName($roleToSet);
        }

        if (!empty($data['active'])) {
            $this->setActive($data['active']);
        }

        if (!empty($data['registration_date'])) {
            $registrationDate = new \DateTime($data['registration_date']);
            $this->setRegistrationDate($registrationDate);
        }

        if (!empty($data['pre_register_validated_date'])) {
            $preRegisterValidateDate = new \DateTime($data['pre_register_validated_date']);
            $this->setPreRegisterValidatedDate($preRegisterValidateDate);
        }

        if (array_key_exists('is_primary', $data)) {
            $this->setIsPrimary($data['is_primary']);
        }

        return $this;
    }
}
