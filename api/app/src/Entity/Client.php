<?php

namespace App\Entity;

use App\Entity\Ndr\Ndr;
use App\Entity\Report\Report;
use App\Entity\Traits\CreateUpdateTimestamps;
use App\Entity\Traits\IsSoftDeleteableEntity;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Client.
 *
 * @ORM\Table(
 *     name="client",
 *     indexes={
 *
 *       @ORM\Index(name="case_number_idx", columns={"case_number"}),
 *       @ORM\Index(name="archived_at_idx", columns={"archived_at"})
 *     },
 *     options={"collate":"utf8_general_ci", "charset":"utf8"}
 *     )
 *
 * @ORM\Entity(repositoryClass="App\Repository\ClientRepository")
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 *
 * @ORM\HasLifecycleCallbacks()
 */
class Client implements ClientInterface
{
    use CreateUpdateTimestamps;
    use IsSoftDeleteableEntity;

    /**
     * @var int
     *
     * @JMS\Groups({"related","basic", "client", "client-id"})
     *
     * @JMS\Type("integer")
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\SequenceGenerator(sequenceName="client_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @JMS\Groups({"client-users"})
     *
     * @JMS\Type("ArrayCollection<App\Entity\User>")
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="clients", fetch="EXTRA_LAZY")
     *
     * @ORM\JoinTable(name="deputy_case",
     *         joinColumns={@ORM\JoinColumn(name="client_id", referencedColumnName="id", onDelete="CASCADE")},
     *         inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")}
     *     )
     */
    private $users;

    /**
     * @JMS\Groups({"client-reports"})
     *
     * @JMS\Type("ArrayCollection<App\Entity\Report\Report>")
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Report\Report", mappedBy="client", cascade={"persist", "remove"})
     *
     * @ORM\OrderBy({"submitDate"="DESC"})
     */
    private $reports;

    /**
     * @JMS\Groups({"basic", "client-ndr", "ndr_id"})
     *
     * @JMS\Type("App\Entity\Ndr\Ndr")
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Ndr\Ndr", mappedBy="client", cascade={"persist", "remove"})
     **/
    private $ndr;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"client", "client-case-number"})
     *
     * @var string
     *
     * @ORM\Column(name="case_number", type="string", length=20, nullable=true)
     */
    private $caseNumber;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"client", "client-email"})
     *
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=60, nullable=true, unique=false)
     */
    private $email;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"client"})
     *
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     */
    private $phone;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"client"})
     *
     * @ORM\Column(name="address", type="string", length=200, nullable=true)
     */
    private ?string $address = null;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"client"})
     *
     * @ORM\Column(name="address2", type="string", length=200, nullable=true)
     */
    private ?string $address2 = null;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"client"})
     *
     * @ORM\Column(name="address3", type="string", length=200, nullable=true)
     */
    private ?string $address3 = null;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"client"})
     *
     * @ORM\Column(name="address4", type="string", length=200, nullable=true)
     */
    private ?string $address4 = null;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"client"})
     *
     * @ORM\Column(name="address5", type="string", length=200, nullable=true)
     */
    private ?string $address5 = null;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"client"})
     *
     * @var string
     *
     * @ORM\Column(name="postcode", type="string", length=10, nullable=true)
     */
    private $postcode;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"client"})
     *
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=10, nullable=true)
     */
    private $country;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"client", "client-name"})
     *
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=50, nullable=true)
     */
    private $firstname;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"client", "client-name"})
     *
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=50, nullable=true)
     */
    private $lastname;

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @JMS\Groups({"client", "client-court-date", "checklist-information"})
     *
     * @var \DateTime|null
     *
     * @ORM\Column(name="court_date", type="date", nullable=true)
     */
    private $courtDate;

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @JMS\Groups({"client"})
     *
     * @var \DateTime|null
     *
     * @ORM\Column(name="date_of_birth", type="date", nullable=true)
     */
    private $dateOfBirth;

    /**
     * @var ArrayCollection
     *
     * @JMS\Type("ArrayCollection<App\Entity\Note>")
     *
     * @JMS\Groups({"client-notes"})
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Note", mappedBy="client", cascade={"persist", "remove"})
     *
     * @ORM\OrderBy({"createdOn"="DESC"})
     */
    private $notes;

    /**
     * @var ArrayCollection
     *
     * @JMS\Type("ArrayCollection<App\Entity\ClientContact>")
     *
     * @JMS\Groups({"client-clientcontacts"})
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ClientContact", mappedBy="client", cascade={"persist", "remove"})
     *
     * @ORM\OrderBy({"lastName"="ASC"})
     */
    private $clientContacts;

    /**
     * Holds the deputy the client belongs to
     * Loaded from the CSV upload.
     *
     * @var Deputy|null
     *
     * @JMS\Groups({"report-submitted-by", "client-deputy"})
     *
     * @JMS\Type("App\Entity\Deputy")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Deputy", inversedBy="clients", fetch="EAGER")
     *
     * @ORM\JoinColumn(name="deputy_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $deputy;

    /**
     * @var ArrayCollection
     *
     * @JMS\Type("ArrayCollection<App\Entity\CourtOrder>")
     *
     * @ORM\OneToMany(targetEntity="App\Entity\CourtOrder", mappedBy="client", cascade={"persist", "remove"})
     *
     * @ORM\OrderBy({"createdAt"="DESC"})
     */
    private $courtOrders;

    /**
     * @var \DateTime|null
     *
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     *
     * @JMS\Groups({"client"})
     *
     * @ORM\Column(name="archived_at", type="datetime", nullable=true)
     */
    private $archivedAt;

    /**
     * @var Organisation|null
     *
     * @JMS\Type("App\Entity\Organisation")
     *
     * @JMS\Groups({"client-organisations"})
     *
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="clients")
     */
    private $organisation;

    /**
     * Constructor.
     */
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
     * @return Collection
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
     * @return ArrayCollection<Report>|Report[]
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * @param Report[] $reports
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
     *
     * @return Report|null
     */
    public function getReportByEndDate(\DateTime $endDate)
    {
        return $this->reports->filter(function ($report) use ($endDate) {
            return $endDate->format('Y-m-d') == $report->getEndDate()->format('Y-m-d');
        })->first();
    }

    /**
     * Get un-submitted reports, ordered by most recently submitted first.
     *
     *  //TODO refactor using OrderBy({"submitDate"="DESC"}) on client.reports
     *
     * @return ArrayCollection
     */
    public function getSubmittedReports()
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
     *
     * @JMS\VirtualProperty
     *
     * @JMS\Type("App\Entity\Report\Report")
     *
     * @JMS\SerializedName("current_report")
     *
     * @JMS\Groups({"current-report"})
     *
     * @return Report|null
     */
    public function getCurrentReport()
    {
        foreach ($this->getReports() as $r) {
            if (empty($r->getSubmitted()) && empty($r->getUnSubmitDate())) {
                return $r;
            }
        }
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
     * @return Ndr
     */
    public function getNdr()
    {
        return $this->ndr;
    }

    public function setNdr(?Ndr $ndr = null)
    {
        $this->ndr = $ndr;
    }

    /**
     * Return full name, e.g. Mr John Smith.
     */
    public function getFullName($space = '&nbsp;')
    {
        return $this->getFirstname().$space.$this->getLastname();
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
     * @param ArrayCollection $notes
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
     * @param ArrayCollection $clientContacts
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
     * @JMS\VirtualProperty
     *
     * @JMS\Type("integer")
     *
     * @JMS\SerializedName("total_report_count")
     *
     * @JMS\Groups({"total-report-count"})
     *
     * @return int
     */
    public function getTotalReportCount()
    {
        return count($this->getReports());
    }

    /**
     * @JMS\VirtualProperty
     *
     * @JMS\Type("integer")
     *
     * @JMS\Groups({"unsubmitted-reports-count"})
     *
     * @return int
     */
    public function getUnsubmittedReportsCount()
    {
        return count($this->getUnsubmittedReports());
    }

    /**
     * @JMS\Exclude()
     *
     * @return Collection<Report>
     */
    public function getUnsubmittedReports()
    {
        return $this->getReports()->filter(function (Report $report) {
            return empty($report->getSubmitted());
        });
    }

    /**
     * Generates the expected Report Start date based on the Court date.
     *
     * @JMS\VirtualProperty
     *
     * @var \DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @JMS\SerializedName("expected_report_start_date")
     *
     * @JMS\Groups({"checklist-information"})
     *
     * @return \DateTime|null
     */
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
     * @JMS\VirtualProperty
     *
     * @var \DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @JMS\SerializedName("expected_report_end_date")
     *
     * @JMS\Groups({"checklist-information"})
     *
     * @return \DateTime|null
     */
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
     * @JMS\VirtualProperty
     *
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     *
     * @JMS\SerializedName("active_from")
     *
     * @JMS\Groups({"active-period"})
     *
     * @return \DateTime
     */
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
     * @return Organisation|null
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

    public function getCourtOrders(): ArrayCollection
    {
        return $this->courtOrders;
    }

    /**
     * @return $this
     */
    public function setCourtOrders(ArrayCollection $courtOrders)
    {
        $this->courtOrders = $courtOrders;

        return $this;
    }
}
