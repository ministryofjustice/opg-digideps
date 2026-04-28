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
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Client.
 */
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

    /**
     * @var int
     */
    #[JMS\Groups(['related', 'basic', 'client', 'client-id'])]
    #[JMS\Type('integer')]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'client_id_seq', allocationSize: 1, initialValue: 1)]
    private $id;

    #[JMS\Groups(['client-users'])]
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\User>')]
    #[ORM\JoinTable(name: 'deputy_case')]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'clients', fetch: 'EXTRA_LAZY')]
    private $users;

    #[JMS\Groups(['client-reports'])]
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\Report>')]
    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Report::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['submitDate' => 'DESC'])]
    private $reports;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['client', 'client-case-number', 'deputy-court-order-basic'])]
    #[ORM\Column(name: 'case_number', type: 'string', length: 20, nullable: true)]
    private $caseNumber;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['client', 'client-email'])]
    #[ORM\Column(name: 'email', type: 'string', length: 60, unique: false, nullable: true)]
    private $email;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['client'])]
    #[ORM\Column(name: 'phone', type: 'string', length: 20, nullable: true)]
    private $phone;

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

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['client'])]
    #[ORM\Column(name: 'postcode', type: 'string', length: 10, nullable: true)]
    private $postcode;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['client'])]
    #[ORM\Column(name: 'country', type: 'string', length: 10, nullable: true)]
    private $country;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['client', 'client-name', 'deputy-court-order-basic'])]
    #[ORM\Column(name: 'firstname', type: 'string', length: 50, nullable: true)]
    private $firstname;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['client', 'client-name', 'deputy-court-order-basic'])]
    #[ORM\Column(name: 'lastname', type: 'string', length: 50, nullable: true)]
    private $lastname;

    /**
     * @var ?\DateTime
     */
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['client', 'client-court-date', 'checklist-information'])]
    #[ORM\Column(name: 'court_date', type: 'date', nullable: true)]
    private $courtDate;

    /**
     * @var ?\DateTime
     */
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['client'])]
    #[ORM\Column(name: 'date_of_birth', type: 'date', nullable: true)]
    private $dateOfBirth;

    /**
     * @var Collection<int, Note>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Note>')]
    #[JMS\Groups(['client-notes'])]
    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Note::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdOn' => 'DESC'])]
    private $notes;

    /**
     * @var Collection<int, ClientContact>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\ClientContact>')]
    #[JMS\Groups(['client-clientcontacts'])]
    #[ORM\OneToMany(mappedBy: 'client', targetEntity: ClientContact::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['lastName' => 'ASC'])]
    private $clientContacts;

    /**
     * Holds the deputy the client belongs to
     * Loaded from the CSV upload.
     *
     * @var ?Deputy
     */
    #[JMS\Groups(['report-submitted-by', 'client-deputy'])]
    #[JMS\Type('OPG\Digideps\Backend\Entity\Deputy')]
    #[ORM\JoinColumn(name: 'deputy_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Deputy::class, fetch: 'EAGER', inversedBy: 'clients')]
    private $deputy;

    /**
     * @var Collection<int, CourtOrder>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\CourtOrder>')]
    #[ORM\OneToMany(mappedBy: 'client', targetEntity: CourtOrder::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private $courtOrders;

    /**
     * @var ?\DateTime
     */
    #[JMS\Type("DateTime<'Y-m-d H:i:s'>")]
    #[JMS\Groups(['client'])]
    #[ORM\Column(name: 'archived_at', type: 'datetime', nullable: true)]
    private $archivedAt;

    /**
     * @var ?Organisation
     */
    #[JMS\Type('OPG\Digideps\Backend\Entity\Organisation')]
    #[JMS\Groups(['client-organisations'])]
    #[ORM\ManyToOne(targetEntity: Organisation::class, inversedBy: 'clients')]
    private $organisation;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->clientContacts = new ArrayCollection();
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

    public function setCaseNumber(?string $caseNumber): self
    {
        $this->caseNumber = $caseNumber ? strtolower($caseNumber) : null;

        return $this;
    }

    public function getCaseNumber(): ?string
    {
        return $this->caseNumber;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return Client
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
     * Set phone.
     *
     * @param string $phone
     *
     * @return Client
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone.
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    public function setAddress(?string $address)
    {
        $this->address = $address;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * Set postcode.
     *
     * @param string $postcode
     *
     * @return Client
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * Get postcode.
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * Set firstname.
     *
     * @param string $firstname
     *
     * @return Client
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
     * Set lastname.
     *
     * @param string $lastname
     *
     * @return Client
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
     * Set courtDate.
     *
     * @return Client
     */
    public function setCourtDate(?\DateTime $courtDate = null)
    {
        $this->courtDate = $courtDate;

        return $this;
    }

    /**
     * Get courtDate.
     *
     * @return \DateTime|null
     */
    public function getCourtDate()
    {
        return $this->courtDate;
    }

    /**
     * Add users.
     *
     * @return Client
     */
    public function addUser(User $user)
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    /**
     * Remove users.
     */
    public function removeUser(User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users.
     *
     * @return Collection<int, User>
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param array $users
     *
     * @return $this
     */
    public function setUsers($users)
    {
        $this->users = $users;

        return $this;
    }

    /**
     * @return array $userIds
     */
    public function getUserIds()
    {
        $userIds = [];

        if (!empty($this->users)) {
            foreach ($this->users as $user) {
                $userIds[] = $user->getId();
            }
        }

        return $userIds;
    }

    /**
     * Add reports.
     *
     * @return Client
     */
    public function addReport(Report $report)
    {
        if (!$this->reports->contains($report)) {
            $this->reports->add($report);
        }
        $report->setClient($this);

        return $this;
    }

    /**
     * Remove reports.
     */
    public function removeReport(Report $reports)
    {
        $this->reports->removeElement($reports);
    }

    /**
     * Get reports.
     *
     * @return ArrayCollection<Report>
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * @param ArrayCollection<int, Report> $reports
     *
     * @return Client
     */
    public function setReports($reports)
    {
        $this->reports = $reports;

        return $this;
    }

    /**
     * Get report by end date.
     */
    public function getReportByEndDate(\DateTime $endDate): ?Report
    {
        $report = $this->reports->filter(function ($report) use ($endDate) {
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
        $arrayIterator = $this->reports->filter(function ($report) {
            return $report->getSubmitted();
        })->getIterator();

        // Sort by submitted date so the most recently submitted are first
        $arrayIterator->uasort(function ($first, $second) {
            return $first->getSubmitDate() < $second->getSubmitDate() ? 1 : -1;
        });

        return new ArrayCollection(iterator_to_array($arrayIterator));
    }

    /**
     * get progress the user is currenty work on
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
     * @return array $reportIds
     */
    public function getReportIds()
    {
        $reportIds = [];

        if (!empty($this->reports)) {
            foreach ($this->reports as $report) {
                $reportIds[] = $report->getId();
            }
        }

        return $reportIds;
    }

    /**
     * Return full name, e.g. Mr John Smith.
     */
    public function getFullName($space = '&nbsp;')
    {
        return $this->getFirstname() . $space . $this->getLastname();
    }

    public function setAddress2(?string $address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function setAddress3(?string $address3)
    {
        $this->address3 = $address3;

        return $this;
    }

    public function getAddress3(): ?string
    {
        return $this->address3;
    }

    public function setAddress4(?string $address4): Client
    {
        $this->address4 = $address4;

        return $this;
    }

    public function getAddress4(): ?string
    {
        return $this->address4;
    }

    public function setAddress5(?string $address5): Client
    {
        $this->address5 = $address5;

        return $this;
    }

    public function getAddress5(): ?string
    {
        return $this->address5;
    }

    /**
     * Set country.
     *
     * @param string $country
     *
     * @return Client
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return bool
     */
    public function hasDetails()
    {
        return !empty($this->getAddress());
    }

    /**
     * @return \DateTime|null $dateOfBirth
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @return $this
     */
    public function setDateOfBirth(?\DateTime $dateOfBirth = null)
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param ArrayCollection<int, Note> $notes
     *
     * @return $this
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    public function getClientContacts()
    {
        return $this->clientContacts;
    }

    /**
     * @param ArrayCollection<int, ClientContact> $clientContacts
     *
     * @return $this
     */
    public function setClientContacts($clientContacts)
    {
        $this->clientContacts = $clientContacts;

        return $this;
    }

    /**
     * Regular expression to match a case number.
     *
     * @param string $query
     *
     * @return bool
     */
    public static function isValidCaseNumber($query)
    {
        return (bool) preg_match('/^[0-9t]{8}$/i', $query);
    }

    public function getDeputy(): ?Deputy
    {
        return $this->deputy;
    }

    /**
     * @return Client
     */
    public function setDeputy(?Deputy $deputy)
    {
        $this->deputy = $deputy;

        return $this;
    }

    /**
     * @return int
     */
    #[JMS\VirtualProperty]
    #[JMS\Type('integer')]
    #[JMS\SerializedName('total_report_count')]
    #[JMS\Groups(['total-report-count'])]
    public function getTotalReportCount()
    {
        return count($this->getReports());
    }

    /**
     * @return int
     */
    #[JMS\VirtualProperty]
    #[JMS\Type('integer')]
    #[JMS\Groups(['unsubmitted-reports-count'])]
    public function getUnsubmittedReportsCount()
    {
        return count($this->getUnsubmittedReports());
    }

    /**
     * @return ArrayCollection<int, Report>
     */
    #[JMS\Exclude]
    public function getUnsubmittedReports()
    {
        return $this->getReports()->filter(function (Report $report) {
            return empty($report->getSubmitted());
        });
    }

    /**
     * Generates the expected Report Start date based on the Court date.
     *
     * @return \DateTime|null
     */
    #[JMS\VirtualProperty]
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\SerializedName('expected_report_start_date')]
    #[JMS\Groups(['checklist-information'])]
    public function getExpectedReportStartDate($year = null)
    {
        if (is_null($this->getCourtDate())) {
            return null;
        }

        // Default year to current
        if (!isset($year)) {
            $year = date('Y');
        }

        $expectedReportStartDate = clone $this->getCourtDate();

        // if court Date is this year, just return it as the start date
        if ($expectedReportStartDate->format('Y') == $year) {
            return $this->getCourtDate();
        }

        // else make it last year
        $expectedReportStartDate->setDate($year - 1, intval($expectedReportStartDate->format('m')), intval($expectedReportStartDate->format('d')));

        return $expectedReportStartDate;
    }

    /**
     * Generates the expected Report End date based on the Court date.
     *
     * @return \DateTime|null
     */
    #[JMS\VirtualProperty]
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\SerializedName('expected_report_end_date')]
    #[JMS\Groups(['checklist-information'])]
    public function getExpectedReportEndDate($year = null)
    {
        if (!($this->getExpectedReportStartDate($year) instanceof \DateTime)) {
            return null;
        }
        $expectedReportEndDate = clone $this->getExpectedReportStartDate($year);

        return $expectedReportEndDate->modify('+1year -1day');
    }

    public function setArchivedAt(?\DateTime $archivedAt = null)
    {
        $this->archivedAt = $archivedAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getArchivedAt()
    {
        return $this->archivedAt;
    }

    /**
     * @return bool
     */
    public function hasDeputies()
    {
        return !$this->getUsers()->isEmpty();
    }

    /**
     * @return bool
     */
    public function hasLayDeputy()
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
     *
     * @return \DateTime
     */
    #[JMS\VirtualProperty]
    #[JMS\Type("DateTime<'Y-m-d H:i:s'>")]
    #[JMS\SerializedName('active_from')]
    #[JMS\Groups(['active-period'])]
    public function getActiveFrom()
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

    /**
     * @return ?Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @return $this
     */
    public function setOrganisation(?Organisation $organisation)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function userBelongsToClientsOrganisation(UserInterface $user)
    {
        if ($this->getOrganisation() instanceof OrganisationInterface && $this->getOrganisation()->isActivated()) {
            return $this->getOrganisation()->containsUser($user);
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
     *
     * @return $this
     */
    public function setCourtOrders(Collection $courtOrders)
    {
        $this->courtOrders = $courtOrders;

        return $this;
    }
}
