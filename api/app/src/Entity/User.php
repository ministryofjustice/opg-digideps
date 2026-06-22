<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Domain\Deputy\DeputyType;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\Traits\AddressTrait;
use OPG\Digideps\Backend\Entity\Traits\CreateUpdateTimestamps;
use OPG\Digideps\Backend\Entity\UserResearch\UserResearchResponse;
use OPG\Digideps\Backend\Repository\UserRepository;
use OPG\Digideps\Backend\Utility\Query\Hydrator;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Table(name: 'dd_user')]
#[ORM\Index(columns: ['created_by_id'], name: 'created_by_idx')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use CreateUpdateTimestamps;
    use AddressTrait;

    public const int ACTIVATE_TOKEN_EXPIRE_HOURS = 48;

    public const string ROLE_ADMIN = 'ROLE_ADMIN';
    public const string ROLE_ADMIN_MANAGER = 'ROLE_ADMIN_MANAGER';
    public const string ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    public const string ROLE_DEPUTY = 'ROLE_DEPUTY';
    public const string ROLE_LAY_DEPUTY = 'ROLE_LAY_DEPUTY';

    public const string ROLE_ORG_NAMED = 'ROLE_ORG_NAMED';
    public const string ROLE_ORG_ADMIN = 'ROLE_ORG_ADMIN';
    public const string ROLE_ORG_TEAM_MEMBER = 'ROLE_ORG_TEAM_MEMBER';

    public const string ROLE_PA = 'ROLE_PA';
    public const string ROLE_PA_NAMED = 'ROLE_PA_NAMED';
    public const string ROLE_PA_ADMIN = 'ROLE_PA_ADMIN';
    public const string ROLE_PA_TEAM_MEMBER = 'ROLE_PA_TEAM_MEMBER';

    public const string ROLE_PROF = 'ROLE_PROF';
    public const string ROLE_PROF_NAMED = 'ROLE_PROF_NAMED';
    public const string ROLE_PROF_ADMIN = 'ROLE_PROF_ADMIN';
    public const string ROLE_PROF_TEAM_MEMBER = 'ROLE_PROF_TEAM_MEMBER';

    public const string TYPE_LAY = 'LAY';
    public const string TYPE_PA = 'PA';
    public const string TYPE_PROF = 'PROF';

    /**
     * @var array<string> $adminRoles
     */
    public static array $adminRoles = [
        self::ROLE_ADMIN,
        self::ROLE_SUPER_ADMIN,
        self::ROLE_ADMIN_MANAGER,
    ];

    /**
     * @var array<string> $caseManagerRoles
     */
    public static array $caseManagerRoles = [
        self::ROLE_ADMIN,
        self::ROLE_ADMIN_MANAGER,
    ];

    /**
     * @var array<string> $orgRoles
     */
    public static array $orgRoles = [
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

    public const string SELF_REGISTER = 'SELF_REGISTER';
    public const string ADMIN_INVITE = 'ADMIN_INVITE';
    public const string ORG_ADMIN_INVITE = 'ORG_ADMIN_INVITE';
    public const string CO_DEPUTY_INVITE = 'CO_DEPUTY_INVITE';
    public const string UNKNOWN_REGISTRATION_ROUTE = 'UNKNOWN';

    #[JMS\Type('integer')]
    #[JMS\Groups(['user', 'report-submitted-by', 'user-id', 'user-list'])]
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'user_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    /**
     * @var Collection<int, Client> $clients
     */
    #[JMS\Groups(['user-clients'])]
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Client>')]
    #[ORM\ManyToMany(targetEntity: Client::class, mappedBy: 'users', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private Collection $clients;

    /**
     * @var Collection<int, Organisation> $organisations
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Organisation>')]
    #[JMS\Groups(['user-organisations'])]
    #[JMS\Accessor(getter: 'getOrganisations')]
    #[ORM\ManyToMany(targetEntity: Organisation::class, mappedBy: 'users', fetch: 'EXTRA_LAZY')]
    private Collection $organisations;

    #[JMS\Type('string')]
    #[JMS\Groups(['user', 'report-submitted-by', 'user-name', 'user-list'])]
    #[ORM\Column(name: 'firstname', type: 'string', length: 100, nullable: false)]
    private string $firstname;

    #[JMS\Type('string')]
    #[JMS\Groups(['user', 'report-submitted-by', 'user-name', 'user-list'])]
    #[ORM\Column(name: 'lastname', type: 'string', length: 100, nullable: false)]
    private string $lastname;

    #[JMS\Groups(['user-login'])]
    #[JMS\Exclude]
    #[ORM\Column(name: 'password', type: 'string', length: 100, nullable: false)]
    private ?string $password = '';

    #[JMS\Groups(['user', 'report-submitted-by', 'user-email', 'user-list'])]
    #[JMS\Type('string')]
    #[ORM\Column(name: 'email', type: 'string', length: 60, unique: true, nullable: false)]
    private string $email;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['user', 'user-list'])]
    #[ORM\Column(name: 'active', type: 'boolean', nullable: true, options: ['default' => false])]
    private ?bool $active = false;

    #[ORM\Column(name: 'salt', type: 'string', length: 100, nullable: true)]
    private ?string $salt = null;

    #[JMS\Type("DateTime<'Y-m-d H:i:s'>")]
    #[JMS\Groups(['user'])]
    #[ORM\Column(name: 'registration_date', type: 'datetime', nullable: true)]
    private ?\DateTime $registrationDate = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['user'])]
    #[ORM\Column(name: 'registration_token', type: 'string', length: 100, nullable: true)]
    private ?string $registrationToken = null;

    #[JMS\Type("DateTime<'Y-m-d H:i:s'>")]
    #[JMS\Groups(['user'])]
    #[ORM\Column(name: 'token_date', type: 'datetime', nullable: true)]
    private ?\DateTime $tokenDate = null;

    /**
     * @var null|string $roleName ROLE_: see roles in Role class
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['user', 'report-submitted-by', 'user-rolename', 'user-list', 'team-users'])]
    #[ORM\Column(name: 'role_name', type: 'string', length: 50, nullable: true)]
    private ?string $roleName = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['user', 'report-submitted-by', 'user-list', 'user-phone-main'])]
    #[ORM\Column(name: 'phone_main', type: 'string', length: 20, nullable: true)]
    private ?string $phoneMain = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['user', 'report-submitted-by'])]
    #[ORM\Column(name: 'phone_alternative', type: 'string', length: 20, nullable: true)]
    private ?string $phoneAlternative = null;

    #[JMS\Type("DateTime<'Y-m-d H:i:s'>")]
    #[JMS\Groups(['user'])]
    #[ORM\Column(name: 'last_logged_in', type: 'datetime', nullable: true)]
    private ?\DateTime $lastLoggedIn = null;

    #[JMS\Type('integer')]
    #[JMS\Groups(['user'])]
    #[ORM\Column(name: 'deputy_uid', type: 'bigint', nullable: true)]
    private ?int $deputyUid = null;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['user'])]
    #[ORM\Column(name: 'ad_managed', type: 'boolean', nullable: true, options: ['default' => false])]
    private ?bool $adManaged = false;

    #[JMS\Type('string')]
    #[JMS\Groups(['user', 'user-list'])]
    #[ORM\Column(name: 'job_title', type: 'string', length: 150, nullable: true)]
    private ?string $jobTitle = null;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['user'])]
    #[ORM\Column(name: 'agree_terms_use', type: 'boolean', nullable: true, options: ['default' => false])]
    private ?bool $agreeTermsUse = false;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['user'])]
    #[ORM\Column(name: 'agree_terms_use_date', type: 'datetime', nullable: true)]
    private ?\DateTime $agreeTermsUseDate = null;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['user'])]
    #[ORM\Column(name: 'codeputy_client_confirmed', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $coDeputyClientConfirmed = false;

    /**
     * @var Collection<int,UserResearchResponse>
     */
    #[JMS\Type('OPG\Digideps\Backend\Entity\UserResearch\UserResearchResponse')]
    #[JMS\Groups(['user', 'satisfaction', 'user-research'])]
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserResearchResponse::class, cascade: ['persist'])]
    private Collection $userResearchResponse;

    #[JMS\Type('OPG\Digideps\Backend\Entity\User')]
    #[JMS\Groups(['user', 'created-by'])]
    #[JMS\MaxDepth(3)]
    #[ORM\JoinColumn(name: 'created_by_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'user')]
    private ?User $createdBy = null;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['user'])]
    #[ORM\Column(name: 'deletion_protection', type: 'boolean', nullable: true, options: ['default' => null])]
    private ?bool $deletionProtection = null;

    #[JMS\Type('OPG\Digideps\Backend\Entity\Deputy')]
    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Deputy::class)]
    private ?Deputy $deputy = null;

    #[JMS\Type("DateTime<'Y-m-d H:i:s'>")]
    #[JMS\Groups(['user'])]
    #[ORM\Column(name: 'pre_register_validated', type: 'datetime', nullable: true)]
    private ?\DateTime $preRegisterValidatedDate = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['user'])]
    #[ORM\Column(name: 'registration_route', type: 'string', length: 30, nullable: false, options: ['default' => 'UNKNOWN'])]
    private string $registrationRoute = self::UNKNOWN_REGISTRATION_ROUTE;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['user'])]
    #[ORM\Column(name: 'is_primary', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isPrimary = false;

    public function __construct(string $firstname, string $lastname, string $email, bool $coDeputyClientConfirmed = false)
    {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->clients = new ArrayCollection();
        $this->organisations = new ArrayCollection();
        $this->userResearchResponse = new ArrayCollection();
        $this->setCoDeputyClientConfirmed($coDeputyClientConfirmed);
    }

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function setId(int $id): static
    {
        if ($this->id === null) {
            $this->id = $id;
        } elseif ($id === 0) {
            throw new \DomainException('You may not set the id of an entity to zero.');
        } else {
            throw new \LogicException('You may not set the id of an entity more than once.');
        }

        return $this;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function setEmail(string $email): static
    {
        $this->email = strtolower($email);

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getActive(): bool
    {
        return $this->active ?? false;
    }

    public function setRegistrationDate(\DateTime $registrationDate): static
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    public function getRegistrationDate(): ?\DateTime
    {
        return $this->registrationDate;
    }

    public function recreateRegistrationToken(): static
    {
        $userIdWithLeadingZeros = sprintf('%08d', $this->getId());
        $token = bin2hex(random_bytes(16)) . $userIdWithLeadingZeros;

        $this->setRegistrationToken($token);
        $this->setTokenDate(new \DateTime());

        return $this;
    }

    public function setRegistrationToken(?string $registrationToken): static
    {
        $this->registrationToken = $registrationToken;

        return $this;
    }

    public function getRegistrationToken(): ?string
    {
        return $this->registrationToken;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setTokenDate(\DateTime $tokenDate): static
    {
        $this->tokenDate = $tokenDate;

        return $this;
    }

    public function getTokenDate(): ?\DateTime
    {
        return $this->tokenDate;
    }

    public function addClient(Client $client): static
    {
        $client->addUser($this);
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
        }

        return $this;
    }

    /**
     * @return Collection<int, Client>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    /**
     * @return Collection<int, Organisation>
     */
    public function getOrganisations(): Collection
    {
        return $this->organisations->filter(function ($organisation): bool {
            return $organisation->isActivated();
        });
    }

    public function addOrganisation(Organisation $organisation): static
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

    public function getRoleName(): string
    {
        return $this->roleName ?? User::ROLE_LAY_DEPUTY;
    }

    /**
     * @param string $roleName ROLE_.*
     */
    public function setRoleName(string $roleName): static
    {
        $this->roleName = $roleName;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        return [$this->getRoleName()];
    }

    public function eraseCredentials()
    {
    }

    public function getFullName(): string
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function getPhoneMain(): ?string
    {
        return $this->phoneMain;
    }

    public function setPhoneMain(?string $phoneMain): static
    {
        $this->phoneMain = $phoneMain;

        return $this;
    }

    public function getLastLoggedIn(): ?\DateTime
    {
        return $this->lastLoggedIn;
    }

    public function setLastLoggedIn(?\DateTime $lastLoggedIn): static
    {
        $this->lastLoggedIn = $lastLoggedIn;

        return $this;
    }

    /**
     * convert 7 into 00000007.
     */
    public static function padDeputyNumber($deputyNo): string
    {
        return str_pad($deputyNo, 8, '0', STR_PAD_LEFT);
    }

    public function getDeputyUid(): ?int
    {
        return $this->deputyUid;
    }

    public function setDeputyUid(null|int|string $deputyUid): static
    {
        $this->deputyUid = $deputyUid === null ? null : (int)$deputyUid;

        return $this;
    }

    /**
     * Return Id of the client (if it has details).
     */
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('id_of_client_with_details')]
    #[JMS\Groups(['user'])]
    #[JMS\Type('integer')]
    public function getIdOfClientWithDetails(): ?int
    {
        return $this->getFirstClient() && $this->getFirstClient()->hasDetails()
            ? $this->getFirstClient()->getId()
            : null;
    }

    #[JMS\VirtualProperty]
    #[JMS\Groups(['user-login'])]
    #[JMS\Type('integer')]
    #[JMS\SerializedName('active_report_id')]
    public function getActiveReportId(): ?int
    {
        $firstClient = $this->getFirstClient();
        if ($firstClient === null) {
            return null;
        }

        $firstUnsubmittedReport = $firstClient->getUnsubmittedReports()->first();
        if (!$firstUnsubmittedReport instanceof Report) {
            return null;
        }

        return $firstUnsubmittedReport->getId();
    }

    #[JMS\VirtualProperty]
    #[JMS\Groups(['user'])]
    #[JMS\Type('integer')]
    #[JMS\SerializedName('number_of_reports')]
    public function getNumberOfReports(): ?int
    {
        return $this->getFirstClient() ? count($this->getFirstClient()->getReports()) : 0;
    }

    #[JMS\VirtualProperty]
    #[JMS\Groups(['user'])]
    #[JMS\Type('integer')]
    #[JMS\SerializedName('number_of_submitted_reports')]
    public function getNumberOfSubmittedReports(): ?int
    {
        $firstClient = $this->getFirstClient();

        if ($firstClient === null) {
            return 0;
        }

        return count($firstClient->getReports()->filter(
            fn ($report) => $report->getSubmitDate() !== null
        ));
    }

    public function getFirstClient(): ?Client
    {
        return $this->getClients()->first() ?: null;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function setJobTitle(?string $jobTitle): static
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    public function getAgreeTermsUse(): bool
    {
        return $this->agreeTermsUse ?? false;
    }

    public function setAgreeTermsUse(?bool $agreeTermsUse): User
    {
        $this->agreeTermsUse = $agreeTermsUse;

        if ($agreeTermsUse) {
            $this->agreeTermsUseDate = new \DateTime('now');
        }

        return $this;
    }

    public function getAgreeTermsUseDate(): ?\DateTime
    {
        return $this->agreeTermsUseDate;
    }

    public function getCoDeputyClientConfirmed(): bool
    {
        return $this->coDeputyClientConfirmed;
    }

    public function setCoDeputyClientConfirmed(bool $coDeputyClientConfirmed): User
    {
        $this->coDeputyClientConfirmed = $coDeputyClientConfirmed;

        return $this;
    }

    /**
     * Return true if the client has other users.
     */
    #[JMS\VirtualProperty]
    #[JMS\Type('boolean')]
    #[JMS\SerializedName('is_co_deputy')]
    #[JMS\Groups(['user'])]
    public function isCoDeputy(): bool
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
     */
    public function isPaDeputy(): bool
    {
        return $this->isPaNamedDeputy() || $this->isPaAdministrator() || $this->isPaTeamMember() || $this->isPaTopRole();
    }

    /**
     * Is a Professional Deputy (any role)?
     */
    public function isProfDeputy(): bool
    {
        return $this->isProfNamedDeputy() || $this->isProfAdministrator() || $this->isProfTeamMember() || $this->isProfTopRole();
    }

    /**
     * Is Organisation Named deputy?
     */
    public function isOrgNamedDeputy(): bool
    {
        return $this->isPaNamedDeputy() || $this->isProfNamedDeputy();
    }

    /**
     * Is PA Named deputy?
     */
    public function isPaNamedDeputy(): bool
    {
        return $this->getRoleName() === self::ROLE_PA_NAMED;
    }

    /**
     * Is PA Named deputy?
     */
    public function isProfNamedDeputy(): bool
    {
        return $this->getRoleName() === self::ROLE_PROF_NAMED;
    }

    public function isLayDeputy(): bool
    {
        return $this->getRoleName() === self::ROLE_LAY_DEPUTY;
    }

    /**
     * Is PA Administrator?
     */
    public function isPaAdministrator(): bool
    {
        return $this->roleName === self::ROLE_PA_ADMIN;
    }

    /**
     * Is user a Professional Administrator?
     */
    public function isProfAdministrator(): bool
    {
        return $this->roleName === self::ROLE_PROF_ADMIN;
    }

    /**
     * Is Organisation Administrator?
     */
    public function isOrgAdministrator(): bool
    {
        return $this->isPaAdministrator() || $this->isProfAdministrator();
    }

    /**
     * Is PA Team member?
     */
    public function isPaTeamMember(): bool
    {
        return $this->getRoleName() === self::ROLE_PA_TEAM_MEMBER;
    }

    /**
     * Is Professional Team member?
     */
    public function isProfTeamMember(): bool
    {
        return $this->getRoleName() === self::ROLE_PROF_TEAM_MEMBER;
    }

    public function isPaTopRole(): bool
    {
        return $this->getRoleName() === self::ROLE_PA;
    }

    public function isProfTopRole(): bool
    {
        return $this->getRoleName() === self::ROLE_PROF;
    }

    public function isOrgNamedOrAdmin(): bool
    {
        return $this->isOrgNamedDeputy() || $this->isOrgAdministrator();
    }

    /**
     * Is user an organisation Team Member?
     */
    public function isOrgTeamMember(): bool
    {
        return $this->isPaTeamMember() || $this->isProfTeamMember();
    }

    /**
     * Is user an Organisation deputy? Any role. PA or Org.
     */
    public function isDeputyOrg(): bool
    {
        return $this->isOrgNamedDeputy() || $this->isOrgAdministrator() || $this->isOrgTeamMember();
    }

    public function isAdmin(): bool
    {
        return $this->getRoleName() === self::ROLE_ADMIN;
    }

    public function isSuperAdmin(): bool
    {
        return $this->getRoleName() === self::ROLE_SUPER_ADMIN;
    }

    public function isAdminManager(): bool
    {
        return $this->getRoleName() === self::ROLE_ADMIN_MANAGER;
    }

    public function hasAdminRole(): bool
    {
        return $this->isAdmin() || $this->isSuperAdmin() || $this->isAdminManager();
    }

    /**
     * Set role to team member.
     */
    public function setDefaultRoleIfEmpty(): void
    {
        if (empty($this->getRoleName())) {
            if ($this->isProfDeputy()) {
                $this->setRoleName(User::ROLE_PROF_TEAM_MEMBER);
            } elseif ($this->isPaDeputy()) {
                $this->setRoleName(User::ROLE_PA_TEAM_MEMBER);
            }
        }
    }

    public function hasReports(): bool
    {
        $firstClient = $this->getFirstClient();

        if ($firstClient === null) {
            return false;
        }

        return !$firstClient->getReports()->isEmpty();
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

    /**
     * @return Collection<int,UserResearchResponse>
     */
    public function getUserResearchResponse(): Collection
    {
        return $this->userResearchResponse;
    }

    /**
     * @param Collection<int,UserResearchResponse> $userResearchResponse
     */
    public function setUserResearchResponse(Collection $userResearchResponse): User
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
        return $user->getRegistrationDate() < new \DateTime()->setTime(00, 00, 00);
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

    public function setPreRegisterValidatedDate(?\DateTime $preRegisterValidatedDate): User
    {
        $this->preRegisterValidatedDate = $preRegisterValidatedDate;

        return $this;
    }

    public function getPreRegisterValidatedDate(): ?\DateTime
    {
        return $this->preRegisterValidatedDate;
    }

    public function getRegistrationRoute(): string
    {
        return $this->registrationRoute;
    }

    public function setRegistrationRoute(string $registrationRoute): User
    {
        $this->registrationRoute = $registrationRoute;

        return $this;
    }

    public function setIsPrimary(bool $primary): User
    {
        $this->isPrimary = $primary;

        return $this;
    }

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
            'ad_managed' => 'setAdManaged',
            'role_name' => 'setRoleName',
            'job_title' => 'setJobTitle',
            'co_deputy_client_confirmed' => 'setCoDeputyClientConfirmed',
        ];

        Hydrator::hydrateEntityWithArrayData($this, $data, $keySetters);

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

    public function deriveDeputyType(): ?DeputyType
    {
        if ($this->roleName !== null) {
            if (str_contains($this->roleName, 'LAY')) {
                return DeputyType::LAY;
            } elseif (str_contains($this->roleName, 'PROF')) {
                return DeputyType::PRO;
            } elseif (str_contains($this->roleName, 'PA')) {
                return DeputyType::PA;
            }
        }
        return null;
    }
}
