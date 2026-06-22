<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\Traits\CreateUpdateTimestamps;
use OPG\Digideps\Backend\Entity\Traits\IsSoftDeleteableEntity;
use OPG\Digideps\Backend\Repository\ClientRepository;

#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Table(name: 'client', options: ['collate' => 'utf8_general_ci', 'charset' => 'utf8'])]
#[ORM\Index(columns: ['case_number'], name: 'case_number_idx')]
#[ORM\Index(columns: ['archived_at'], name: 'archived_at_idx')]
#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Client
{
    use CreateUpdateTimestamps;
    use IsSoftDeleteableEntity;

    #[JMS\Groups(['related', 'basic', 'client', 'client-id'])]
    #[JMS\Type('integer')]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'client_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    /**
     * @var Collection<int, User>
     */
    #[JMS\Groups(['client-users'])]
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\User>')]
    #[ORM\JoinTable(name: 'deputy_case')]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'clients', fetch: 'EXTRA_LAZY')]
    private Collection $users;

    /**
     * @var Collection<int, Report>
     */
    #[JMS\Groups(['client-reports'])]
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\Report>')]
    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Report::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['submitDate' => 'DESC'])]
    private Collection $reports;

    #[JMS\Type('string')]
    #[JMS\Groups(['client', 'client-case-number', 'deputy-court-order-basic'])]
    #[ORM\Column(name: 'case_number', type: 'string', length: 20, nullable: true)]
    private ?string $caseNumber = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['client', 'client-email'])]
    #[ORM\Column(name: 'email', type: 'string', length: 60, unique: false, nullable: true)]
    private ?string $email = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['client'])]
    #[ORM\Column(name: 'phone', type: 'string', length: 20, nullable: true)]
    private ?string $phone = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['client'])]
    #[ORM\Column(name: 'address', type: 'string', length: 200, nullable: true)]
    private ?string $address = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['client'])]
    #[ORM\Column(name: 'address2', type: 'string', length: 200, nullable: true)]
    private ?string $address2 = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['client'])]
    #[ORM\Column(name: 'address3', type: 'string', length: 200, nullable: true)]
    private ?string $address3 = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['client'])]
    #[ORM\Column(name: 'address4', type: 'string', length: 200, nullable: true)]
    private ?string $address4 = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['client'])]
    #[ORM\Column(name: 'address5', type: 'string', length: 200, nullable: true)]
    private ?string $address5 = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['client'])]
    #[ORM\Column(name: 'postcode', type: 'string', length: 10, nullable: true)]
    private ?string $postcode = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['client'])]
    #[ORM\Column(name: 'country', type: 'string', length: 10, nullable: true)]
    private ?string $country = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['client', 'client-name', 'deputy-court-order-basic'])]
    #[ORM\Column(name: 'firstname', type: 'string', length: 50, nullable: true)]
    private ?string $firstname = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['client', 'client-name', 'deputy-court-order-basic'])]
    #[ORM\Column(name: 'lastname', type: 'string', length: 50, nullable: true)]
    private ?string $lastname = null;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['client', 'client-court-date', 'checklist-information'])]
    #[ORM\Column(name: 'court_date', type: 'date', nullable: true)]
    private ?\DateTime $courtDate = null;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['client'])]
    #[ORM\Column(name: 'date_of_birth', type: 'date', nullable: true)]
    private ?\DateTime $dateOfBirth = null;

    /**
     * @var Collection<int, Note>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Note>')]
    #[JMS\Groups(['client-notes'])]
    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Note::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdOn' => 'DESC'])]
    private Collection $notes;

    /**
     * @var Collection<int, ClientContact>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\ClientContact>')]
    #[JMS\Groups(['client-clientcontacts'])]
    #[ORM\OneToMany(mappedBy: 'client', targetEntity: ClientContact::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['lastName' => 'ASC'])]
    private Collection $clientContacts;

    /**
     * Holds the deputy the client belongs to
     * Loaded from the CSV upload.
     */
    #[JMS\Groups(['report-submitted-by', 'client-deputy'])]
    #[JMS\Type('OPG\Digideps\Backend\Entity\Deputy')]
    #[ORM\JoinColumn(name: 'deputy_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Deputy::class, fetch: 'EAGER', inversedBy: 'clients')]
    private ?Deputy $deputy = null;

    /**
     * @var Collection<int, CourtOrder>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\CourtOrder>')]
    #[ORM\OneToMany(mappedBy: 'client', targetEntity: CourtOrder::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $courtOrders;

    #[JMS\Type("DateTime<'Y-m-d H:i:s'>")]
    #[JMS\Groups(['client'])]
    #[ORM\Column(name: 'archived_at', type: 'datetime', nullable: true)]
    private ?\DateTime $archivedAt = null;

    #[JMS\Type('OPG\Digideps\Backend\Entity\Organisation')]
    #[JMS\Groups(['client-organisations'])]
    #[ORM\ManyToOne(targetEntity: Organisation::class, inversedBy: 'clients')]
    private ?Organisation $organisation = null;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->clientContacts = new ArrayCollection();
        $this->courtOrders = new ArrayCollection();
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

    public function setCaseNumber(?string $caseNumber): static
    {
        $this->caseNumber = $caseNumber ? strtolower($caseNumber) : null;

        return $this;
    }

    public function getCaseNumber(): ?string
    {
        return $this->caseNumber;
    }

    public function setEmail(?string $email): static
    {
        $this->email = (($email === null) ? null : strtolower($email));

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setPostcode(?string $postcode): static
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setCourtDate(?\DateTime $courtDate): static
    {
        $this->courtDate = $courtDate;

        return $this;
    }

    public function getCourtDate(): ?\DateTime
    {
        return $this->courtDate;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $users): void
    {
        $this->users->removeElement($users);
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param Collection<int, User> $users
     */
    public function setUsers(Collection $users): static
    {
        $this->users = $users;

        return $this;
    }

    /**
     * @return array<int>
     */
    public function getUserIds(): array
    {
        $userIds = [];

        foreach ($this->users as $user) {
            $userIds[] = $user->getId();
        }

        return $userIds;
    }

    public function addReport(Report $report): static
    {
        if (!$this->reports->contains($report)) {
            $this->reports->add($report);
        }
        $report->setClient($this);

        return $this;
    }

    public function removeReport(Report $reports): void
    {
        $this->reports->removeElement($reports);
    }

    /**
     * @return Collection<int, Report>
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    /**
     * @param Collection<int, Report> $reports
     */
    public function setReports(Collection $reports): static
    {
        $this->reports = $reports;

        return $this;
    }

    public function getReportByEndDate(\DateTime $endDate): ?Report
    {
        $report = $this->reports->filter(function ($report) use ($endDate): bool {
            return $endDate->format('Y-m-d') == $report->getEndDate()->format('Y-m-d');
        })->first();

        if (!$report) {
            return null;
        }

        return $report;
    }

    /**
     * Get un-submitted reports, ordered by most recently submitted first.
     *
     *  //TODO refactor using OrderBy({"submitDate"="DESC"}) on client.reports
     *
     * @return Collection<int, Report>
     */
    public function getSubmittedReports(): Collection
    {
        $reports = $this->reports->filter(function (Report $report): bool {
            return $report->getSubmitted() === true;
        })->toArray();

        // Sort by submitted date so the most recently submitted are first
        uasort($reports, function ($first, $second): int {
            return $first->getSubmitDate() < $second->getSubmitDate() ? 1 : -1;
        });

        return new ArrayCollection($reports);
    }

    /**
     * get progress the user is currently work on
     * That means the first one that is unsubmitted AND has an unsubmit date.
     */
    #[JMS\VirtualProperty]
    #[JMS\Type('OPG\Digideps\Backend\Entity\Report\Report')]
    #[JMS\SerializedName('current_report')]
    #[JMS\Groups(['current-report'])]
    public function getCurrentReport(): ?Report
    {
        foreach ($this->getReports() as $r) {
            if (empty($r->getSubmitted()) && empty($r->getUnSubmitDate())) {
                return $r;
            }
        }
        return null;
    }

    /**
     * @return array<int> $reportIds
     */
    public function getReportIds(): array
    {
        $reportIds = [];

        foreach ($this->reports as $report) {
            $reportIds[] = $report->getId();
        }

        return $reportIds;
    }

    /**
     * Return full name, e.g. Mr John Smith.
     */
    public function getFullName(string $space = '&nbsp;'): string
    {
        return $this->getFirstname() . $space . $this->getLastname();
    }

    public function setAddress2(?string $address2): static
    {
        $this->address2 = $address2;

        return $this;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function setAddress3(?string $address3): static
    {
        $this->address3 = $address3;

        return $this;
    }

    public function getAddress3(): ?string
    {
        return $this->address3;
    }

    public function setAddress4(?string $address4): static
    {
        $this->address4 = $address4;

        return $this;
    }

    public function getAddress4(): ?string
    {
        return $this->address4;
    }

    public function setAddress5(?string $address5): static
    {
        $this->address5 = $address5;

        return $this;
    }

    public function getAddress5(): ?string
    {
        return $this->address5;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function hasDetails(): bool
    {
        return !empty($this->getAddress());
    }

    public function getDateOfBirth(): ?\DateTime
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?\DateTime $dateOfBirth): static
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /**
     * @return Collection<int, Note>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    /**
     * @param Collection<int, Note> $notes
     */
    public function setNotes(Collection $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return Collection<int, ClientContact>
     */
    public function getClientContacts(): Collection
    {
        return $this->clientContacts;
    }

    /**
     * @param Collection<int, ClientContact> $clientContacts
     */
    public function setClientContacts(Collection $clientContacts): static
    {
        $this->clientContacts = $clientContacts;

        return $this;
    }

    /**
     * Regular expression to match a case number.
     */
    public static function isValidCaseNumber(string $query): bool
    {
        return (bool) preg_match('/^[0-9t]{8}$/i', $query);
    }

    public function getDeputy(): ?Deputy
    {
        return $this->deputy;
    }

    public function setDeputy(?Deputy $deputy): static
    {
        $this->deputy = $deputy;

        return $this;
    }

    #[JMS\VirtualProperty]
    #[JMS\Type('integer')]
    #[JMS\SerializedName('total_report_count')]
    #[JMS\Groups(['total-report-count'])]
    public function getTotalReportCount(): int
    {
        return count($this->getReports());
    }

    #[JMS\VirtualProperty]
    #[JMS\Type('integer')]
    #[JMS\Groups(['unsubmitted-reports-count'])]
    public function getUnsubmittedReportsCount(): int
    {
        return count($this->getUnsubmittedReports());
    }

    /**
     * @return Collection<int, Report>
     */
    #[JMS\Exclude]
    public function getUnsubmittedReports(): Collection
    {
        return $this->getReports()->filter(function (Report $report): bool {
            return $report->getSubmitted() === null || $report->getSubmitted() === false;
        });
    }

    /**
     * Generates the expected Report Start date based on the Court date.
     */
    #[JMS\VirtualProperty]
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\SerializedName('expected_report_start_date')]
    #[JMS\Groups(['checklist-information'])]
    public function getExpectedReportStartDate(?int $year = null): ?\DateTime
    {
        if (is_null($this->getCourtDate())) {
            return null;
        }

        // Default year to current
        $year ??= (int)date('Y');

        $expectedReportStartDate = clone $this->getCourtDate();

        // if court Date is this year, just return it as the start date
        if ((int)$expectedReportStartDate->format('Y') === $year) {
            return $this->getCourtDate();
        }

        // else make it last year
        $expectedReportStartDate->setDate($year - 1, intval($expectedReportStartDate->format('m')), intval($expectedReportStartDate->format('d')));

        return $expectedReportStartDate;
    }

    /**
     * Generates the expected Report End date based on the Court date.
     */
    #[JMS\VirtualProperty]
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\SerializedName('expected_report_end_date')]
    #[JMS\Groups(['checklist-information'])]
    public function getExpectedReportEndDate(?int $year = null): ?\DateTime
    {
        if (!($this->getExpectedReportStartDate($year) instanceof \DateTime)) {
            return null;
        }
        $expectedReportEndDate = clone $this->getExpectedReportStartDate($year);

        return $expectedReportEndDate->modify('+1year -1day');
    }

    public function setArchivedAt(?\DateTime $archivedAt = null): static
    {
        $this->archivedAt = $archivedAt;

        return $this;
    }

    public function getArchivedAt(): ?\DateTime
    {
        return $this->archivedAt;
    }

    public function hasDeputies(): bool
    {
        return !$this->getUsers()->isEmpty();
    }

    public function hasLayDeputy(): bool
    {
        if (!$this->hasDeputies()) {
            return false;
        }

        foreach ($this->getUsers() as $user) {
            if ($user->isLayDeputy()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get Active From date == earliest report start date for this client.
     */
    #[JMS\VirtualProperty]
    #[JMS\Type("DateTime<'Y-m-d H:i:s'>")]
    #[JMS\SerializedName('active_from')]
    #[JMS\Groups(['active-period'])]
    public function getActiveFrom(): \DateTime
    {
        $reports = $this->getReports();
        $earliest = new \DateTime('now');
        foreach ($reports as $report) {
            if ($report->getStartDate() < $earliest) {
                $earliest = $report->getStartDate();
            }
        }

        return $earliest;
    }

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(?Organisation $organisation): static
    {
        $this->organisation = $organisation;

        return $this;
    }

    public function userBelongsToClientsOrganisation(User $user): bool
    {
        $org = $this->getOrganisation();
        if ($org instanceof Organisation && $org->isActivated()) {
            return $org->containsUser($user);
        }

        return false;
    }

    /**
     * @return Collection<int, CourtOrder>
     */
    public function getCourtOrders(): Collection
    {
        return $this->courtOrders;
    }

    /**
     * @param Collection<int, CourtOrder> $courtOrders
     */
    public function setCourtOrders(Collection $courtOrders): static
    {
        $this->courtOrders = $courtOrders;

        return $this;
    }

    public function filterReports(int ...$reportIds): void
    {
        $this->reports = $this->reports->filter(fn (Report $report) => in_array($report->getId(), $reportIds));
    }
}
