<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\Status;
use AppBundle\Entity\Traits\IsSoftDeleteableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Client.
 *
 * @ORM\Table(
 *     name="client",
 *     indexes={
 *       @ORM\Index(name="case_number_idx", columns={"case_number"}),
 *       @ORM\Index(name="archived_at_idx", columns={"archived_at"})
 *     },
 *     options={"collate":"utf8_general_ci", "charset":"utf8"}
 *     )
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\ClientRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Client implements ClientInterface
{
    use IsSoftDeleteableEntity;

    /**
     * @var int
     *
     * @JMS\Groups({"related","basic", "client", "client-id"})
     * @JMS\Type("integer")
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="client_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @JMS\Groups({"client-users"})
     * @JMS\Type("array")
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", inversedBy="clients", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="deputy_case",
     *         joinColumns={@ORM\JoinColumn(name="client_id", referencedColumnName="id", onDelete="CASCADE")},
     *         inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")}
     *     )
     */
    private $users;

    /**
     * @JMS\Groups({"client-reports"})
     * @JMS\Type("array")
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\Report", mappedBy="client", cascade={"persist", "remove"})
     */
    private $reports;

    /**
     * @JMS\Groups({"basic", "client-ndr", "ndr_id"})
     * @JMS\Type("AppBundle\Entity\Ndr\Ndr")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Ndr\Ndr", mappedBy="client", cascade={"persist", "remove"})
     **/
    private $ndr;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"client", "client-case-number"})
     *
     * @var string
     *
     * @ORM\Column(name="case_number", type="string", length=20, nullable=true)
     */
    private $caseNumber;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"client", "client-email"})
     *
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=60, nullable=true, unique=false)
     */
    private $email;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"client"})
     *
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     */
    private $phone;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"client"})
     *
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=200, nullable=true)
     */
    private $address;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"client"})
     *
     * @var string
     *
     * @ORM\Column(name="address2", type="string", length=200, nullable=true)
     */
    private $address2;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"client"})
     *
     * @var string
     *
     * @ORM\Column(name="county", type="string", length=75, nullable=true)
     */
    private $county;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"client"})
     *
     * @var string
     *
     * @ORM\Column(name="postcode", type="string", length=10, nullable=true)
     */
    private $postcode;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"client"})
     *
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=10, nullable=true)
     */
    private $country;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"client", "client-name"})
     *
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=50, nullable=true)
     */
    private $firstname;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"client", "client-name"})
     *
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=50, nullable=true)
     */
    private $lastname;

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"client", "client-court-date", "checklist-information"})
     *
     * @var \Date
     *
     * @ORM\Column(name="court_date", type="date", nullable=true)
     */
    private $courtDate;

    /**
     * @JMS\Exclude
     *
     * @var \DateTime
     *
     * @ORM\Column(name="last_edit", type="datetime", nullable=true)
     */
    private $lastedit;

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"client"})
     *
     * @var \Date
     *
     * @ORM\Column(name="date_of_birth", type="date", nullable=true)
     */
    private $dateOfBirth;

    /**
     * @var ArrayCollection
     *
     * @JMS\Type("ArrayCollection<AppBundle\Entity\Note>")
     * @JMS\Groups({"client-notes"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Note", mappedBy="client", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdOn"="DESC"})
     */
    private $notes;

    /**
     * @var ArrayCollection
     *
     * @JMS\Type("ArrayCollection<AppBundle\Entity\ClientContact>")
     * @JMS\Groups({"client-clientcontacts"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ClientContact", mappedBy="client", cascade={"persist", "remove"})
     * @ORM\OrderBy({"lastName"="ASC"})
     */
    private $clientContacts;

    /**
     * Holds the named deputy the client belongs to
     * Loaded from the CSV upload
     *
     * @var User
     *
     * @JMS\Groups({"report-submitted-by", "client-named-deputy"})
     * @JMS\Type("AppBundle\Entity\NamedDeputy")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\NamedDeputy", inversedBy="clients", fetch="EAGER")
     * @ORM\JoinColumn(name="named_deputy_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $namedDeputy;

    /**
     * @var \DateTime
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * @JMS\Groups({"client"})
     *
     * @ORM\Column(name="archived_at", type="datetime", nullable=true)
     */
    private $archivedAt;

    /**
     * @var Organisation
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

    /**
     * Set caseNumber.
     *
     * @param string $caseNumber
     *
     * @return Client
     */
    public function setCaseNumber($caseNumber)
    {
        // normalise case number in order to understand if it's already used when registering and checking with CASREC
        $this->caseNumber = CasRec::normaliseCaseNumber($caseNumber);

        return $this;
    }

    /**
     * Get caseNumber.
     *
     * @return string
     */
    public function getCaseNumber()
    {
        return $this->caseNumber;
    }

    /**
     * convert 7 into 00000007
     * One Lay deputy has a case number starting with zeros
     *
     * @param $caseNumber
     *
     * @return string
     */
    public static function padCaseNumber($caseNumber)
    {
        return str_pad($caseNumber, 8, '0', STR_PAD_LEFT);
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

    /**
     * Set address.
     *
     * @param string $address
     *
     * @return Client
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address.
     *
     * @return string
     */
    public function getAddress()
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
     * @param \DateTime $courtDate
     *
     * @return Client
     */
    public function setCourtDate(\DateTime $courtDate = null)
    {
        $this->courtDate = $courtDate;

        return $this;
    }

    /**
     * Get courtDate.
     *
     * @return \DateTime
     */
    public function getCourtDate()
    {
        return $this->courtDate;
    }

    /**
     * Set lastedit.
     *
     * @param \DateTime $lastedit
     *
     * @return Client
     */
    public function setLastedit($lastedit)
    {
        $this->lastedit = $lastedit;

        return $this;
    }

    /**
     * Get lastedit.
     *
     * @return \DateTime
     */
    public function getLastedit()
    {
        return $this->lastedit;
    }

    /**
     * Add users.
     *
     * @param User $user
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
     *
     * @param User $users
     */
    public function removeUser(User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param $users
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
     * @param Report $reports
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
     *
     * @param Report $reports
     */
    public function removeReport(Report $reports)
    {
        $this->reports->removeElement($reports);
    }

    /**
     * Get reports.
     *
     * @return Report[]
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * Get report by end date
     *
     * @param \DateTime $endDate
     *
     * @return Report
     */
    public function getReportByEndDate(\DateTime $endDate)
    {
        return $this->reports->filter(function ($report) use ($endDate) {
            return $endDate->format('Y-m-d') == $report->getEndDate()->format('Y-m-d');
        })->first();
    }

    /**
     * Get un-submitted reports, ordered by most recently submitted first
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

        # Sort by submitted date so the most recently submitted are first
        $arrayIterator->uasort(function ($first, $second) {
            return $first->getSubmitDate() < $second->getSubmitDate() ? 1 : -1;
        });

        return new ArrayCollection(iterator_to_array($arrayIterator));
    }



    /**
     * get progress the user is currenty work on
     * That means the first one that is unsubmitted AND has an unsubmit date
     *
     * @JMS\VirtualProperty
     * @JMS\Type("AppBundle\Entity\Report\Report")
     * @JMS\SerializedName("current_report")
     * @JMS\Groups({"current-report"})
     *
     * @return Report|null
     */
    public function getCurrentReport()
    {
        foreach($this->getReports() as $r) {
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

    /**
     * @param mixed $ndr
     */
    public function setNdr(Ndr $ndr = null)
    {
        $this->ndr = $ndr;
    }

    /**
     * Return full name, e.g. Mr John Smith.
     */
    public function getFullName($space = '&nbsp;')
    {
        return $this->getFirstname() . $space . $this->getLastname();
    }

    /**
     * Set address2.
     *
     * @param string $address2
     *
     * @return Client
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * Get address2.
     *
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * Set county.
     *
     * @param string $county
     *
     * @return Client
     */
    public function setCounty($county)
    {
        $this->county = $county;

        return $this;
    }

    /**
     * Get county.
     *
     * @return string
     */
    public function getCounty()
    {
        return $this->county;
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
     * @return \DateTime $dateOfBirth
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @param \DateTime $dateOfBirth
     *
     * @return \AppBundle\Entity\User
     */
    public function setDateOfBirth(\DateTime $dateOfBirth = null)
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param $notes
     *
     * @return $this
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getClientContacts()
    {
        return $this->clientContacts;
    }

    /**
     * @param $clientContacts
     *
     * @return $this
     */
    public function setClientContacts($clientContacts)
    {
        $this->clientContacts = $clientContacts;
        return $this;
    }

    /**
     * Regular expression to match a case number
     *
     * @param $query
     *
     * @return bool
     */
    public static function isValidCaseNumber($query)
    {
        return (bool) preg_match('/^[0-9t]{8}$/i', $query);
    }

    /**
     * @return NamedDeputy
     */
    public function getNamedDeputy()
    {
        return $this->namedDeputy;
    }

    /**
     * @param NamedDeputy $namedDeputy
     * @return Client
     */
    public function setNamedDeputy(NamedDeputy $namedDeputy)
    {
        $this->namedDeputy = $namedDeputy;
        return $this;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("integer")
     * @JMS\SerializedName("total_report_count")
     * @JMS\Groups({"total-report-count"})
     *
     * @return integer
     */
    public function getTotalReportCount()
    {
        return count($this->getReports());
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Type("integer")
     * @JMS\Groups({"unsubmitted-reports-count"})
     *
     * @return integer
     */
    public function getUnsubmittedReportsCount()
    {
        return count($this->getUnsubmittedReports());
    }

    /**
     * @JMS\Exclude()
     *
     * @return Report[]
     */
    public function getUnsubmittedReports()
    {
        return $this->getReports()->filter(function (Report $report) {
            return empty($report->getSubmitted());
        });
    }


    /**
     * Generates the expected Report Start date based on the Court date
     * @JMS\VirtualProperty
     * @var DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\SerializedName("expected_report_start_date")
     * @JMS\Groups({"checklist-information"})
     * @return \DateTime|null
     */
    public function getExpectedReportStartDate($year = NULL)
    {
        // Default year to current
        if (!isset($year)) {
            $year = date('Y');
        }

        // clone datetime object. Do not alter object courtDate property.
        /** @var \DateTime $expectedReportStartDate */
        if (!($this->getCourtDate() instanceof \DateTime)) {
            if ($this->getCalculatedCourtDate() instanceof \DateTime) {
                $expectedReportStartDate = clone $this->getCalculatedCourtDate();
            } else {
                // nothing to use for expected start date
                return null;
            }
        } else {
            $expectedReportStartDate = clone $this->getCourtDate();
        }

        // if court Date is this year, just return it as the start date
        if ($expectedReportStartDate->format('Y') == $year) {
            return $this->getCourtDate();
        }

        // else make it last year
        $expectedReportStartDate->setDate($year-1, $expectedReportStartDate->format('m'), $expectedReportStartDate->format('d'));

        return $expectedReportStartDate;
    }

    /**
     * Generates the expected Report End date based on the Court date
     * @JMS\VirtualProperty
     * @var DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\SerializedName("expected_report_end_date")
     * @JMS\Groups({"checklist-information"})
     *
     * @return \DateTime|null
     */
    public function getExpectedReportEndDate($year = NULL)
    {
        if (!($this->getExpectedReportStartDate($year) instanceof \DateTime)) {
            return null;
        }
        $expectedReportEndDate = clone $this->getExpectedReportStartDate($year);
        return $expectedReportEndDate->modify('+1year -1day');
    }

    /**
     * Generates the Report Court date based on the first report made by client
     *
     * @JMS\VirtualProperty
     * @var DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\SerializedName("calculated_court_date")
     * @JMS\Groups({"checklist-information"})
     *
     * @return \DateTime|null
     */
    public function getCalculatedCourtDate()
    {
        if ($this->getCourtDate() instanceof \DateTime) {
            return $this->getCourtDate();
        }

        $arrayIterator = $this->reports->filter(function ($report) {
            return $report->getEndDate();
        })->getIterator();

        # Sort by end date so the oldest first
        $arrayIterator->uasort(function ($first, $second) {
            return $first->getEndDate() > $second->getEndDate() ? 1 : -1;
        });

        $orderedReports = iterator_to_array($arrayIterator);

        if (isset($orderedReports[0]) && $orderedReports[0] instanceof Report && $orderedReports[0]->getEndDate() instanceof \DateTime) {
            $calculatedCourtDate = clone $orderedReports[0]->getEndDate();
            return $calculatedCourtDate->modify('-1 year +1 day');
        }

        return null;
    }

    /**
     * @param \DateTime|null $archivedAt
     */
    public function setArchivedAt(\DateTime $archivedAt = null)
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
     * Get Active From date == earliest report start date for this client
     *
     * @JMS\VirtualProperty
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * @JMS\SerializedName("active_from")
     * @JMS\Groups({"active-period"})
     *
     * @return \DateTime
     */
    public function getActiveFrom()
    {
        $reports = $this->getReports();
        $earliest = new \DateTime('now');
        foreach ($reports as $report)
        {
            if ($report->getStartDate() < $earliest) {
                $earliest = $report->getStartDate();
            }
        }

        return $earliest;
    }

    /**
     * Get Active To date
     *
     * @JMS\VirtualProperty
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * @JMS\SerializedName("active_to")
     * @JMS\Groups({"active-period"})
     *
     * @return \DateTime
     */
    public function getActiveTo()
    {
        return $this->getDeletedAt();
    }


    /**
     * @return Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @param Organisation $organisation
     * @return $this
     */
    public function setOrganisation(Organisation $organisation)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function userBelongsToClientsOrganisation(UserInterface $user)
    {
        if ($this->getOrganisation() instanceof OrganisationInterface && $this->getOrganisation()->isActivated()) {
            return $this->getOrganisation()->containsUser($user);
        }
        return false;
    }
}
