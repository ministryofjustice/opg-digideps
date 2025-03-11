<?php

namespace App\Entity;

use App\Entity\Report\Report;
use App\Entity\Traits\ActiveAudit;
use App\Entity\Traits\IsSoftDeleteableEntity;
use App\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AppAssert\YearMustBeFourDigitsAndValid(groups={"client-court-date"})
 */
class Client
{
    use IsSoftDeleteableEntity;
    use ActiveAudit;

    /**
     * @JMS\Type("integer")
     *
     * @JMS\Groups({"edit", "pa-edit", "client-id"})
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"edit", "pa-edit"})
     *
     * @Assert\NotBlank( message="client.firstname.notBlank", groups={"lay-deputy-client", "lay-deputy-client-edit", "pa-client"})
     *
     * @Assert\Length(min=2, minMessage= "client.firstname.minMessage", max=50, maxMessage= "client.firstname.maxMessage", groups={"lay-deputy-client", "pa-client"})
     *
     * @var string
     */
    private $firstname;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"edit", "pa-edit"})
     *
     * @Assert\NotBlank( message="client.lastname.notBlank", groups={"lay-deputy-client", "verify-codeputy", "lay-deputy-client-edit", "pa-client"})
     *
     * @Assert\Length(min = 2, minMessage= "client.lastname.minMessage", max=50, maxMessage= "client.lastname.maxMessage", groups={"lay-deputy-client", "verify-codeputy", "pa-client"})
     *
     * @var string
     */
    private $lastname;

    /**
     * @JMS\Type("array<App\Entity\User>")
     *
     * @var User[]
     */
    private $users = [];

    /**
     * @JMS\Type("App\Entity\Deputy")
     *
     * @var Deputy|null
     */
    private $deputy;

    /**
     * @JMS\Type("array<App\Entity\Report\Report>")
     *
     * @var array
     */
    private $reports = [];

    /**
     * @JMS\Type("App\Entity\Report\Report")
     *
     * @var Report
     */
    private $currentReport;

    /**
     * @var Ndr\Ndr
     *
     * @JMS\Type("App\Entity\Ndr\Ndr")
     */
    private $ndr;

    /**
     * @JMS\Exclude()
     *
     * @var string
     */
    private $fullname;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"edit", "client-case-number"})
     *
     * @Assert\NotBlank( message="client.caseNumber.notBlank", groups={"lay-deputy-client", "verify-codeputy"})
     *
     * @Assert\Regex(
     *       pattern="/^.{8}$|^.{10}$/",
     *       message="client.caseNumber.exactMessage",
     *       groups={"lay-deputy-client", "verify-codeputy"}
     *   )
     *
     * @var string
     */
    private $caseNumber;

    /**
     * @JMS\Accessor(setter="setCourtDateWithoutTime")
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @JMS\Groups({"edit", "client-court-date", "checklist-information"})
     *
     * @Assert\NotBlank( message="client.courtDate.notBlank", groups={"lay-deputy-client", "lay-deputy-client-edit"})
     *
     * @Assert\Type(type="DateTimeInterface", message="client.courtDate.message", groups={"lay-deputy-client", "lay-deputy-client-edit"})
     *
     * @Assert\LessThan("today", groups={"pa-client"}, message="client.courtDate.lessThan", groups={"lay-deputy-client", "lay-deputy-client-edit"})
     *
     * @var \DateTime|null
     */
    private $courtDate;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"edit", "pa-edit"})
     *
     * @Assert\NotBlank( message="client.address.notBlank", groups={"lay-deputy-client", "lay-deputy-client-edit"})
     *
     * @Assert\Length(max=200, maxMessage="client.address.maxMessage", groups={"lay-deputy-client", "pa-client"})
     *
     * @var string
     */
    private $address;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"edit", "pa-edit"})
     *
     * @Assert\Length(max=200, maxMessage="client.address.maxMessage", groups={"lay-deputy-client", "pa-client", "lay-deputy-client-edit"})
     *
     * @var string
     */
    private $address2;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"edit", "pa-edit"})
     *
     * @Assert\Length(max=200, maxMessage="client.address.maxMessage", groups={"lay-deputy-client", "pa-client", "lay-deputy-client-edit"})
     *
     * @var string
     */
    private $address3;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"edit", "pa-edit"})
     *
     * @Assert\Length(max=200, maxMessage="client.address.maxMessage", groups={"lay-deputy-client", "pa-client", "lay-deputy-client-edit"})
     *
     * @var string
     */
    private $address4;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"edit", "pa-edit"})
     *
     * @Assert\Length(max=200, maxMessage="client.address.maxMessage", groups={"lay-deputy-client", "pa-client", "lay-deputy-client-edit"})
     *
     * @var string
     */
    private $address5;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"edit", "pa-edit"})
     *
     * @Assert\NotBlank( message="client.postcode.notBlank", groups={"lay-deputy-client", "lay-deputy-client-edit"})
     *
     * @Assert\Length(max=10, maxMessage= "client.postcode.maxMessage", groups={"lay-deputy-client", "pa-client", "lay-deputy-client-edit"})
     *
     * @var string
     */
    private $postcode;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"edit"})
     *
     * @var string
     */
    private $country;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"edit", "pa-edit"})
     *
     * @Assert\Length(min=10, max=20, minMessage="common.genericPhone.minLength", maxMessage="common.genericPhone.maxLength", groups={"lay-deputy-client", "pa-client", "lay-deputy-client-edit"})
     *
     * @var string
     */
    private $phone;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"pa-edit", "client-email"})
     *
     * @Assert\Email( message="client.email.invalid", groups={"pa-client"})
     *
     * @Assert\Length(max=60, maxMessage="client.email.maxLength", groups={"pa-client"})
     *
     * @var string
     */
    private $email;

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @JMS\Groups({"pa-edit"})
     *
     * @Assert\LessThan("today", groups={"pa-client"}, message="client.dateOfBirth.lessThan")
     *
     * @var \DateTime|null
     */
    private $dateOfBirth;

    /**
     * @var ArrayCollection
     *
     * @JMS\Type("ArrayCollection<App\Entity\Note>")
     *
     * @JMS\Groups({"notes"})
     */
    private $notes;

    /**
     * @var ArrayCollection
     *
     * @JMS\Type("ArrayCollection<App\Entity\ClientContact>")
     *
     * @JMS\Groups({"clientcontacts"})
     */
    private $clientContacts;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     *
     * @JMS\Groups({"total-report-count"})
     */
    private $totalReportCount;

    /**
     * @JMS\Type("App\Entity\Organisation")
     *
     * @var Organisation
     */
    private $organisation;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $unsubmittedReportsCount;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @JMS\Groups({"checklist-information"})
     */
    private $expectedReportStartDate;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @JMS\Groups({"checklist-information"})
     */
    private $expectedReportEndDate;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     */
    private $archivedAt;

    public function __construct()
    {
        $this->users = [];
        $this->reports = [];
    }

    /**
     * @return User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    public function setDeputy(Deputy $deputy): self
    {
        $this->deputy = $deputy;

        return $this;
    }

    /**
     * Return true if the user (based on `getId()` comparison is present among the users.
     * Return false if any of the user is not an instance of the User class or the ID is not present.
     *
     * Mainly used from voters
     *
     * @return bool
     */
    public function hasUser(User $user)
    {
        foreach ($this->users ?: [] as $currentUser) {
            if (
                $user->getId()
                && $currentUser instanceof User && $currentUser->getId()
                && $user->getId() == $currentUser->getId()
            ) {
                return true;
            }
        }

        return false;
    }

    public function setUsers($users)
    {
        $this->users = $users;

        return $this;
    }

    public function addUser($user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * @return array $reports
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * @return array $reports
     */
    public function getReportsSubmittedAtLeastOnce()
    {
        return array_filter($this->getReports() ?: [], function (Report $report) {
            return $report->getSubmitted() || $report->getUnSubmitDate();
        });
    }

    /**
     * @param int $id report ID
     *
     * @return Report|null
     */
    public function getReportById($id)
    {
        foreach ($this->reports as $report) {
            if ($report->getId() == $id) {
                return $report;
            }
        }

        return null;
    }

    /**
     * @param Report $report
     *
     * @return Client
     */
    public function addReport($report)
    {
        $this->reports[] = $report;

        return $this;
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
     * @return Report
     */
    public function getCurrentReport()
    {
        return $this->currentReport;
    }

    /**
     * @param Report $currentReport
     */
    public function setCurrentReport($currentReport): self
    {
        $this->currentReport = $currentReport;

        return $this;
    }

    /**
     * @return Ndr\Ndr
     */
    public function getNdr()
    {
        return $this->ndr;
    }

    /**
     * @param Ndr\Ndr $ndr
     */
    public function setNdr($ndr)
    {
        $this->ndr = $ndr;

        return $this;
    }

    public function removeReport($report)
    {
        if (!empty($this->reports)) {
            foreach ($this->reports as $key => $reportObj) {
                if ($reportObj->getId() == $report->getId()) {
                    unset($this->reports[$key]);

                    return $this;
                }
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasDetails()
    {
        if (!empty($this->getAddress())) {
            return true;
        }
    }

    public function hasReport()
    {
        if (!empty($this->reports)) {
            return true;
        }

        return false;
    }

    public function getFullname()
    {
        $this->fullname = $this->firstname.' '.$this->lastname;

        return $this->fullname;
    }

    public function setCourtDateWithoutTime($courtDate = null)
    {
        $this->courtDate = ($courtDate instanceof \DateTime) ?
                new \DateTime($courtDate->format('Y-m-d')) : null;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Client
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
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
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
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
     * @return string
     */
    public function getCaseNumber()
    {
        return $this->caseNumber;
    }

    /**
     * @param string $caseNumber
     *
     * @return Client
     */
    public function setCaseNumber($caseNumber)
    {
        $this->caseNumber = $caseNumber;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCourtDate()
    {
        return $this->courtDate;
    }

    /**
     * @param \DateTime|null $courtDate
     *
     * @return Client
     */
    public function setCourtDate($courtDate)
    {
        $this->courtDate = $courtDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function setAddress2(?string $address2): self
    {
        $this->address2 = $address2;

        return $this;
    }

    public function getAddress3(): ?string
    {
        return $this->address3;
    }

    public function setAddress3(?string $address3): self
    {
        $this->address3 = $address3;

        return $this;
    }

    public function getAddress4(): ?string
    {
        return $this->address4;
    }

    public function setAddress4(?string $address4): self
    {
        $this->address4 = $address4;

        return $this;
    }

    public function getAddress5(): ?string
    {
        return $this->address5;
    }

    public function setAddress5(?string $address5): self
    {
        $this->address5 = $address5;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
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
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
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
     * @return array
     */
    public function getAddressNotEmptyParts()
    {
        return array_filter([
            $this->address,
            $this->address2,
            $this->address3,
            $this->postcode,
        ]);
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
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
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return Client
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return \DateTime|null $dateOfBirth
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @return Client
     */
    public function setDateOfBirth(?\DateTime $dateOfBirth = null)
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /*
     * @return int
     */
    public function getAge()
    {
        if (!$this->dateOfBirth) {
            return;
        }
        $to = new \DateTime('today');

        return $this->dateOfBirth->diff($to)->y;
    }

    /**
     * @return ArrayCollection
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param ArrayCollection $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    /**
     * @return ArrayCollection
     */
    public function getClientContacts()
    {
        return $this->clientContacts;
    }

    /**
     * @param ArrayCollection $clientContacts
     */
    public function setClientContacts($clientContacts)
    {
        $this->clientContacts = $clientContacts;
    }

    /**
     * @return array $coDeps an array of users sorted by firstname, or email if no firstname
     */
    public function getCoDeputies()
    {
        $coDeps = [];
        if (is_array($this->users) && count($this->users) > 0) {
            foreach ($this->users as $user) {
                if (!$user->getFirstname()) {
                    $matches = [];
                    preg_match('(^\w+)', $user->getEmail(), $matches);
                    if (!empty($matches[0])) {
                        $coDeps[strtolower($matches[0]).$user->getId()] = $user;
                    }
                } else {
                    if (!isnull($user->getFirstname())) {
                        $coDeps[strtolower($user->getFirstname()).$user->getId()] = $user;
                    }
                }
            }
            ksort($coDeps);
        }

        return array_values($coDeps);
    }

    /**
     * @return array $submittedReports an array of submitted reports
     */
    public function getSubmittedReports()
    {
        $submittedReports = [];
        foreach ($this->getReports() as $report) {
            if ($report->isSubmitted()) {
                $submittedReports[] = $report;
            }
        }

        return $submittedReports;
    }

    /**
     * @return Report|null
     */
    public function getActiveReport()
    {
        foreach ($this->getReports() as $report) {
            if (!$report->isSubmitted() && !$report->getUnSubmitDate()) {
                return $report;
            }
        }

        return null;
    }

    /**
     * @return Report|null
     */
    public function getUnsubmittedReport()
    {
        foreach ($this->getReports() as $report) {
            if (!$report->isSubmitted() && $report->getUnSubmitDate()) {
                return $report;
            }
        }

        return null;
    }

    /**
     * @return int
     */
    public function getTotalReportCount()
    {
        return $this->totalReportCount;
    }

    /**
     * @param int $totalReportCount
     */
    public function setTotalReportCount($totalReportCount)
    {
        $this->totalReportCount = $totalReportCount;
    }

    /**
     * @return int
     */
    public function getUnsubmittedReportsCount()
    {
        return $this->unsubmittedReportsCount;
    }

    /**
     * @param int $unsubmittedReportsCount
     *
     * @return Client
     */
    public function setUnsubmittedReportsCount($unsubmittedReportsCount)
    {
        $this->unsubmittedReportsCount = $unsubmittedReportsCount;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpectedReportStartDate()
    {
        return $this->expectedReportStartDate;
    }

    /**
     * @param \DateTime $expectedReportStartDate
     */
    public function setExpectedReportStartDate($expectedReportStartDate)
    {
        $this->expectedReportStartDate = $expectedReportStartDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpectedReportEndDate()
    {
        return $this->expectedReportEndDate;
    }

    /**
     * @param \DateTime $expectedReportEndDate
     *
     * @return $this
     */
    public function setExpectedReportEndDate($expectedReportEndDate)
    {
        $this->expectedReportEndDate = $expectedReportEndDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getArchivedAt()
    {
        return $this->archivedAt;
    }

    /**
     * @return \DateTime
     */
    public function getActiveFrom()
    {
        return $this->activeFrom;
    }

    /**
     * @return \DateTime
     */
    public function getActiveTo()
    {
        return $this->activeTo;
    }

    /**
     * @return Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    public function setOrganisation(Organisation $organisation): void
    {
        $this->organisation = $organisation;
    }

    public function userBelongsToClientsOrganisation(User $user)
    {
        if ($this->getOrganisation() instanceof Organisation && $this->getOrganisation()->isActivated()) {
            foreach ($user->getOrganisations() as $organisation) {
                if ($organisation->getId() === $this->getOrganisation()->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return Deputy|User|null
     */
    public function getDeputy()
    {
        if (!is_null($this->deputy)) {
            return $this->deputy;
        }

        if ($this->getDeletedAt() instanceof \DateTime) {
            return null;
        }

        foreach ($this->getUsers() as $user) {
            if ($user->isLayDeputy() && !is_null($user->getDeputyUid())) {
                return $user;
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
}
