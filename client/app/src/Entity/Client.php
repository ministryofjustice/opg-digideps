<?php

namespace OPG\Digideps\Frontend\Entity;

use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Entity\Traits\ActiveAudit;
use OPG\Digideps\Frontend\Entity\Traits\IsSoftDeleteableEntity;
use OPG\Digideps\Frontend\Validator\Constraints as AppAssert;
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
     * @var int
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['edit', 'pa-edit', 'client-id'])]
    private $id;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['edit', 'pa-edit'])]
    #[Assert\NotBlank(message: 'client.firstname.notBlank', groups: ['lay-deputy-client', 'lay-deputy-client-edit', 'pa-client'])]
    #[Assert\Length(min: 2, minMessage: 'client.firstname.minMessage', max: 50, maxMessage: 'client.firstname.maxMessage', groups: ['lay-deputy-client', 'pa-client'])]
    private $firstname;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['edit', 'pa-edit'])]
    #[Assert\NotBlank(message: 'client.lastname.notBlank', groups: ['lay-deputy-client', 'verify-codeputy', 'lay-deputy-client-edit', 'pa-client'])]
    #[Assert\Length(min: 2, minMessage: 'client.lastname.minMessage', max: 50, maxMessage: 'client.lastname.maxMessage', groups: ['lay-deputy-client', 'verify-codeputy', 'pa-client'])]
    private $lastname;

    /**
     * @var User[]
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\User>')]
    private array $users = [];

    /**
     * @var Deputy|null
     */
    #[JMS\Type('OPG\Digideps\Frontend\Entity\Deputy')]
    private $deputy;

    /**
     * @var Report[]
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\Report>')]
    private array $reports = [];

    /**
     * @var Report
     */
    #[JMS\Type('OPG\Digideps\Frontend\Entity\Report\Report')]
    private $currentReport;

    /**
     * @var string
     */
    #[JMS\Exclude]
    private $fullname;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['edit', 'client-case-number'])]
    #[Assert\NotBlank(message: 'client.caseNumber.notBlank', groups: ['lay-deputy-client', 'verify-codeputy'])]
    #[Assert\Regex(pattern: '/^.{8}$|^.{10}$/', message: 'client.caseNumber.exactMessage', groups: ['lay-deputy-client', 'verify-codeputy'])]
    private $caseNumber;

    /**
     * @var \DateTime|null
     */
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['edit', 'client-court-date', 'checklist-information'])]
    #[JMS\Accessor(setter: 'setCourtDateWithoutTime')]
    #[Assert\NotBlank(message: 'client.courtDate.notBlank', groups: ['lay-deputy-client'])]
    #[Assert\Type(type: 'DateTimeInterface', message: 'client.courtDate.message', groups: ['lay-deputy-client'])]
    #[Assert\LessThan('today', groups: ['lay-deputy-client'], message: 'client.courtDate.lessThan')]
    private $courtDate;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['edit', 'pa-edit'])]
    #[Assert\NotBlank(message: 'client.address.notBlank', groups: ['lay-deputy-client', 'lay-deputy-client-edit'])]
    #[Assert\Length(max: 200, maxMessage: 'client.address.maxMessage', groups: ['lay-deputy-client', 'pa-client'])]
    private $address;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['edit', 'pa-edit'])]
    #[Assert\Length(max: 200, maxMessage: 'client.address.maxMessage', groups: ['lay-deputy-client', 'pa-client', 'lay-deputy-client-edit'])]
    private $address2;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['edit', 'pa-edit'])]
    #[Assert\Length(max: 200, maxMessage: 'client.address.maxMessage', groups: ['lay-deputy-client', 'pa-client', 'lay-deputy-client-edit'])]
    private $address3;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['edit', 'pa-edit'])]
    #[Assert\Length(max: 200, maxMessage: 'client.address.maxMessage', groups: ['lay-deputy-client', 'pa-client', 'lay-deputy-client-edit'])]
    private $address4;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['edit', 'pa-edit'])]
    #[Assert\Length(max: 200, maxMessage: 'client.address.maxMessage', groups: ['lay-deputy-client', 'pa-client', 'lay-deputy-client-edit'])]
    private $address5;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['edit', 'pa-edit'])]
    #[Assert\NotBlank(message: 'client.postcode.notBlank', groups: ['lay-deputy-client', 'lay-deputy-client-edit'])]
    #[Assert\Length(max: 10, maxMessage: 'client.postcode.maxMessage', groups: ['lay-deputy-client', 'pa-client', 'lay-deputy-client-edit'])]
    private $postcode;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['edit'])]
    private $country;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['edit', 'pa-edit'])]
    #[Assert\Length(min: 10, max: 20, minMessage: 'common.genericPhone.minLength', maxMessage: 'common.genericPhone.maxLength', groups: ['lay-deputy-client', 'pa-client', 'lay-deputy-client-edit'])]
    private $phone;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['pa-edit', 'client-email'])]
    #[Assert\Email(message: 'client.email.invalid', groups: ['pa-client'])]
    #[Assert\Length(max: 60, maxMessage: 'client.email.maxLength', groups: ['pa-client'])]
    private $email;

    /**
     * @var \DateTime|null
     */
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['pa-edit'])]
    #[Assert\LessThan('today', message: 'client.dateOfBirth.lessThan', groups: ['pa-client'])]
    private $dateOfBirth;

    /**
     * @var array<Note>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Frontend\Entity\Note>')]
    #[JMS\Groups(['notes'])]
    private array $notes;

    /**
     * @var array<ClientContact>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Frontend\Entity\ClientContact>')]
    #[JMS\Groups(['clientcontacts'])]
    private array $clientContacts = [];

    /**
     * @var int
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['total-report-count'])]
    private $totalReportCount;

    /**
     * @var Organisation
     */
    #[JMS\Type('OPG\Digideps\Frontend\Entity\Organisation')]
    private $organisation;

    /**
     * @var int
     */
    #[JMS\Type('integer')]
    private $unsubmittedReportsCount;

    /**
     * @var \DateTime
     */
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['checklist-information'])]
    private $expectedReportStartDate;

    /**
     * @var \DateTime
     */
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['checklist-information'])]
    private $expectedReportEndDate;

    /**
     * @var \DateTime
     */
    #[JMS\Type("DateTime<'Y-m-d H:i:s'>")]
    private $archivedAt;

    public function __construct()
    {
    }

    /**
     * @return User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    public function setDeputy(Deputy $deputy): static
    {
        $this->deputy = $deputy;

        return $this;
    }

    /**
     * Return true if the user (based on `getId()` comparison is present among the users.
     * Return false if any of the user is not an instance of the User class or the ID is not present.
     *
     * Mainly used from voters
     */
    public function hasUser(User $user): bool
    {
        return array_any($this->users ?: [], fn ($currentUser) =>
            $user->getId()
            && $currentUser instanceof User
            && $currentUser->getId()
            && $user->getId() == $currentUser->getId());
    }

    public function setUsers($users): static
    {
        $this->users = $users;

        return $this;
    }

    public function addUser($user): static
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * @return Report[] $reports
     */
    public function getReports(): array
    {
        return $this->reports;
    }

    /**
     * @return array $reports
     */
    public function getReportsSubmittedAtLeastOnce(): array
    {
        return array_filter($this->getReports() ?: [], function (Report $report): bool {
            return $report->getSubmitted() || $report->getUnSubmitDate();
        });
    }

    /**
     * @param int $id report ID
     */
    public function getReportById($id): ?Report
    {
        return array_find($this->reports, fn ($report) => $report->getId() == $id);
    }

    /**
     * @param Report $report
     */
    public function addReport($report): static
    {
        $this->reports[] = $report;

        return $this;
    }

    /**
     * @param Report[] $reports
     */
    public function setReports(array $reports): static
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
    public function setCurrentReport($currentReport): static
    {
        $this->currentReport = $currentReport;

        return $this;
    }

    public function removeReport($report): static
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

    public function hasDetails(): bool
    {
        return !empty($this->getAddress());
    }

    public function hasReport(): bool
    {
        return !empty($this->reports);
    }

    public function getFullname(): string
    {
        $this->fullname = $this->firstname . ' ' . $this->lastname;

        return $this->fullname;
    }

    public function setCourtDateWithoutTime($courtDate = null): void
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
     */
    public function setId($id): static
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
     */
    public function setFirstname($firstname): static
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
     */
    public function setLastname($lastname): static
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
     */
    public function setCaseNumber($caseNumber): static
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
     */
    public function setCourtDate($courtDate): static
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
    public function setAddress($address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function setAddress2(?string $address2): static
    {
        $this->address2 = $address2;

        return $this;
    }

    public function getAddress3(): ?string
    {
        return $this->address3;
    }

    public function setAddress3(?string $address3): static
    {
        $this->address3 = $address3;

        return $this;
    }

    public function getAddress4(): ?string
    {
        return $this->address4;
    }

    public function setAddress4(?string $address4): static
    {
        $this->address4 = $address4;

        return $this;
    }

    public function getAddress5(): ?string
    {
        return $this->address5;
    }

    public function setAddress5(?string $address5): static
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
     */
    public function setPostcode($postcode): static
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
     */
    public function setCountry($country): static
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return array
     */
    public function getAddressNotEmptyParts(): array
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
     */
    public function setPhone($phone): static
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
     */
    public function setEmail($email): static
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

    public function setDateOfBirth(?\DateTime $dateOfBirth = null): static
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /**
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
     * @return array<Note>
     */
    public function getNotes(): array
    {
        return $this->notes;
    }

    /**
     * @param array<Note>$notes
     */
    public function setNotes(array $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return array<ClientContact>
     */
    public function getClientContacts(): array
    {
        return $this->clientContacts;
    }

    /**
     * @param array<ClientContact> $clientContacts
     */
    public function setClientContacts(array $clientContacts): static
    {
        $this->clientContacts = $clientContacts;

        return $this;
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
        return array_find($this->getReports(), fn ($report) => !$report->isSubmitted() && $report->getUnSubmitDate());
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
    public function setTotalReportCount($totalReportCount): static
    {
        $this->totalReportCount = $totalReportCount;

        return $this;
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
     */
    public function setUnsubmittedReportsCount($unsubmittedReportsCount): static
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
     */
    public function setExpectedReportEndDate($expectedReportEndDate): static
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

    public function setOrganisation(Organisation $organisation): static
    {
        $this->organisation = $organisation;

        return $this;
    }

    public function userBelongsToClientsOrganisation(User $user): bool
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

    /**
     * @return array $coDeps an array of users sorted by firstname, or email if no firstname
     */
    public function getCoDeputies(): array
    {
        $coDeps = [];
        if (is_array($this->users) && count($this->users) > 0) {
            foreach ($this->users as $user) {
                if (!$user->getFirstname()) {
                    $matches = [];
                    preg_match('(^\w+)', $user->getEmail(), $matches);
                    if (!empty($matches[0])) {
                        $coDeps[strtolower($matches[0]) . $user->getId()] = $user;
                    }
                } else {
                    $coDeps[strtolower($user->getFirstname()) . $user->getId()] = $user;
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
}
