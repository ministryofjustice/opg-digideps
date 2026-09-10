<?php

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Client;
use OPG\Digideps\Frontend\Entity\Deputy;
use OPG\Digideps\Frontend\Entity\Report\Traits as ReportTraits;
use OPG\Digideps\Frontend\Entity\User;
use OPG\Digideps\Frontend\Validator\Constraints\EndDateNotBeforeStartDate;
use OPG\Digideps\Frontend\Validator\Constraints\EndDateNotGreaterThanFifteenMonths;
use OPG\Digideps\Frontend\Validator\Constraints\ProfDeputyCostsEstimate\CostBreakdownNotGreaterThanTotal;
use OPG\Digideps\Frontend\Validator\Constraints\StartEndDateComparableInterface;
use OPG\Digideps\Frontend\Validator\Constraints\YearMustBeFourDigitsAndValid;
use Symfony\Component\Validator\Constraints as Assert;

#[CostBreakdownNotGreaterThanTotal(groups: ['prof-deputy-estimate-costs'])]
#[EndDateNotBeforeStartDate(groups: ['start-end-dates'])]
#[EndDateNotGreaterThanFifteenMonths(groups: ['start-end-dates'])]
#[YearMustBeFourDigitsAndValid(groups: ['start-end-dates'])]
#[Assert\Callback(callback: 'debtsValid', groups: ['debts'])]
#[Assert\Callback(callback: 'feesValid', groups: ['fees'])]
#[Assert\Callback(callback: 'profCostsInterimAtLeastOne', groups: ['prof-deputy-interim-costs'])]
#[Assert\Callback(callback: 'unsubmittedSectionAtLeastOnce', groups: ['unsubmitted_sections'])]
class Report implements StartEndDateComparableInterface
{
    use ReportTraits\ReportAssetTrait;
    use ReportTraits\ReportBalanceTrait;
    use ReportTraits\ReportBankAccountsTrait;
    use ReportTraits\ReportTransfersTrait;
    use ReportTraits\ReportDebtsTrait;
    use ReportTraits\ReportDeputyExpenseTrait;
    use ReportTraits\ReportGiftTrait;
    use ReportTraits\ReportMoneyShortTrait;
    use ReportTraits\ReportMoneyTransactionTrait;
    use ReportTraits\ReportMoreInfoTrait;
    use ReportTraits\ReportPaFeeExpensesTrait;
    use ReportTraits\ReportProfServiceFeesTrait;
    use ReportTraits\ReportProfDeputyCostsTrait;
    use ReportTraits\ReportProfDeputyCostsEstimateTrait;
    use ReportTraits\ReportUnsubmittedSections;

    // https://opgtransform.atlassian.net/wiki/spaces/DEPDS/pages/135266255/Report+variations
    public const string LAY_PFA_LOW_ASSETS_TYPE = '103';
    public const string LAY_PFA_HIGH_ASSETS_TYPE = '102';
    public const string LAY_HW_TYPE = '104';
    public const string LAY_COMBINED_LOW_ASSETS_TYPE = '103-4';
    public const string LAY_COMBINED_HIGH_ASSETS_TYPE = '102-4';

    // PA
    public const string PA_PFA_LOW_ASSETS_TYPE = '103-6';
    public const string PA_PFA_HIGH_ASSETS_TYPE = '102-6';
    public const string PA_HW_TYPE = '104-6';
    public const string PA_COMBINED_LOW_ASSETS_TYPE = '103-4-6';
    public const string PA_COMBINED_HIGH_ASSETS_TYPE = '102-4-6';

    // PROF
    public const string PROF_PFA_LOW_ASSETS_TYPE = '103-5';
    public const string PROF_PFA_HIGH_ASSETS_TYPE = '102-5';
    public const string PROF_HW_TYPE = '104-5';
    public const string PROF_COMBINED_LOW_ASSETS_TYPE = '103-4-5';
    public const string PROF_COMBINED_HIGH_ASSETS_TYPE = '102-4-5';

    // Applies to both costs and estimate costs
    public const string PROF_DEPUTY_COSTS_TYPE_FIXED = 'fixed';
    public const string PROF_DEPUTY_COSTS_TYPE_ASSESSED = 'assessed';
    public const string PROF_DEPUTY_COSTS_TYPE_BOTH = 'both';

    public const string STATUS_NOT_STARTED = 'notStarted';
    public const string STATUS_READY_TO_SUBMIT = 'readyToSubmit';

    public const string TYPE_HEALTH_WELFARE = '104';
    public const string TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS = '102';
    public const string TYPE_PROPERTY_AND_AFFAIRS_LOW_ASSETS = '103';
    public const string TYPE_COMBINED_HIGH_ASSETS = '102-4';
    public const string TYPE_COMBINED_LOW_ASSETS = '103-4';

    public const string TYPE_ABBREVIATION_HW = 'HW';
    public const string TYPE_ABBREVIATION_PF = 'PF';
    public const string TYPE_ABBREVIATION_COMBINED = 'COMBINED';

    public const array HIGH_ASSETS_REPORT_TYPES = [
        self::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS,
        self::TYPE_COMBINED_HIGH_ASSETS,
    ];

    public const string HEALTH_AND_WELFARE_REPORT = 'Health and Welfare Report';
    public const string PROPERTY_AND_AFFAIRS_REPORT = 'Property & Affairs Report';
    public const string PROPERTY_AND_AFFAIRS_WITH_HEALTH_AND_WELFARE_REPORT = 'Property & Affairs with Health & Welfare Report';

    // Decisions
    public const string SIGNIFICANT_DECISION_MADE = 'Yes';
    public const string SIGNIFICANT_DECISION_NOT_MADE = 'No';

    // Money in and out exists
    public const string YES_MONEY_EXISTS = 'Yes';
    public const string NO_MONEY_EXISTS = 'No';

    #[JMS\Type('integer')]
    #[JMS\Groups(['visits-care', 'report-id'])]
    private ?int $id = null;

    /**
     * see TYPE_* constants
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report_type'])]
    private ?string $type = null;

    #[JMS\Type('boolean')]
    private bool $has106flag = false;

    #[JMS\Type('boolean')]
    private bool $isDue = false;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['startEndDates'])]
    #[Assert\NotBlank(message: 'report.startDate.notBlank', groups: ['start-end-dates'])]
    #[Assert\Type(type: 'DateTimeInterface', message: 'report.startDate.invalidMessage', groups: ['start-end-dates'])]
    private \DateTime $startDate;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['startEndDates'])]
    #[Assert\NotBlank(message: 'report.endDate.notBlank', groups: ['start-end-dates'])]
    #[Assert\Type(type: 'DateTimeInterface', message: 'report.endDate.invalidMessage', groups: ['start-end-dates'])]
    private \DateTime $endDate;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['report_due_date'])]
    private \DateTime $dueDate;

    #[JMS\Type('DateTime')]
    #[JMS\Groups(['submit'])]
    private ?\DateTime $submitDate = null;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['unsubmit_date'])]
    private ?\DateTime $unSubmitDate = null;

    #[JMS\Type('OPG\Digideps\Frontend\Entity\User')]
    private ?User $submittedBy = null;

    #[JMS\Type('OPG\Digideps\Frontend\Entity\Deputy')]
    #[JMS\Groups(['deputy'])]
    private ?Deputy $primaryDeputy = null;

    /**
     * @var array<ReportSubmission>
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\ReportSubmission>')]
    private array $reportSubmissions;

    #[JMS\Type('OPG\Digideps\Frontend\Entity\Client')]
    private Client $client;

    #[JMS\Exclude]
    private ?string $period = null;

    /**
     * @var array<Contact>
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\Contact>')]
    private array $contacts = [];

    /**
     * @var array<Decision>
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\Decision>')]
    private array $decisions = [];

    #[JMS\Type('OPG\Digideps\Frontend\Entity\Report\VisitsCare')]
    private ?VisitsCare $visitsCare = null;

    #[JMS\Type('OPG\Digideps\Frontend\Entity\Report\Lifestyle')]
    private ?Lifestyle $lifestyle = null;

    #[JMS\Type('OPG\Digideps\Frontend\Entity\Report\Action')]
    private ?Action $action = null;

    #[JMS\Type('OPG\Digideps\Frontend\Entity\Report\MentalCapacity')]
    private ?MentalCapacity $mentalCapacity = null;

    #[JMS\Type('OPG\Digideps\Frontend\Entity\Report\ClientBenefitsCheck')]
    #[Assert\Valid(groups: ['client-benefits-check'])]
    #[JMS\Groups(['client-benefits-check'])]
    private ?ClientBenefitsCheck $clientBenefitsCheck = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['reasonForNoContacts'])]
    #[Assert\NotBlank(message: 'contact.reasonForNoContacts.notBlank', groups: ['reasonForNoContacts'])]
    private ?string $reasonForNoContacts = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'significantDecisionsMade'])]
    private ?string $significantDecisionsMade = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['reasonForNoDecisions'])]
    #[Assert\NotBlank(message: 'decision.reasonForNoDecisions.notBlank', groups: ['reason-no-decisions'])]
    private ?string $reasonForNoDecisions = null;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['noAssetsToAdd'])]
    private ?bool $noAssetToAdd = null;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['submit', 'submitted'])]
    private ?bool $submitted = null;

    #[JMS\Type('boolean')]
    private ?bool $reportSeen = null;

    #[JMS\Type('boolean')]
    #[Assert\IsTrue(message: 'report.agree', groups: ['declare'])]
    private ?bool $agree = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'submit', 'submit_agreed'])]
    #[Assert\NotBlank(message: 'report.agreedBehalfDeputy.notBlank', groups: ['declare'])]
    private ?string $agreedBehalfDeputy = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'submit', 'submit_agreed'])]
    #[Assert\NotBlank(message: 'report.agreedBehalfDeputyExplanation.notBlank', groups: ['declare-explanation'])]
    private ?string $agreedBehalfDeputyExplanation = null;

    /**
     * @var array<Document>
     */
    #[JMS\Groups(['report-documents'])]
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\Document>')]
    private array $documents = [];

    /**
     * @var array<Document>
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\Document>')]
    #[JMS\Groups(['report-documents'])]
    private array $submittedDocuments = [];

    /**
     * @var array<Document>
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\Document>')]
    #[JMS\Groups(['report-documents'])]
    private array $unsubmittedDocuments = [];

    #[JMS\Type('OPG\Digideps\Frontend\Entity\Report\Status')]
    private ?Status $status = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'wish-to-provide-documentation', 'report-documents'])]
    #[Assert\NotBlank(message: 'document.wishToProvideDocumentation.notBlank', groups: ['wish-to-provide-documentation'])]
    private ?string $wishToProvideDocumentation = null;

    /**
     * @var array<string>
     */
    #[JMS\Type('array')]
    private array $availableSections = [];

    #[JMS\Type('OPG\Digideps\Frontend\Entity\Report\Checklist')]
    private ?Checklist $checklist = null;

    #[JMS\Type('OPG\Digideps\Frontend\Entity\Report\ReviewChecklist')]
    private ?ReviewChecklist $reviewChecklist = null;

    #[JMS\Type('array')]
    private array $previousReportData = [];

    #[JMS\Type('string')]
    private ?string $reportTitle = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'doesMoneyInExist'])]
    #[Assert\NotBlank(message: 'moneyIn.moneyInChoice.notBlank', groups: ['doesMoneyInExist'])]
    private ?string $moneyInExists = null;

    /**
     * @var ?string captures reason for no money in. Required if no money has gone in
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'reasonForNoMoneyIn'])]
    #[Assert\NotBlank(message: 'moneyIn.reasonForNoMoneyIn.notBlank', groups: ['reasonForNoMoneyIn'])]
    private ?string $reasonForNoMoneyIn = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'doesMoneyOutExist'])]
    #[Assert\NotBlank(message: 'moneyOut.moneyOutChoice.notBlank', groups: ['doesMoneyOutExist'])]
    private ?string $moneyOutExists = null;

    /**
     * @var ?string captures reason for no money out. Required if no money has gone out
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'reasonForNoMoneyOut'])]
    #[Assert\NotBlank(message: 'moneyOut.reasonForNoMoneyOut.notBlank', groups: ['reasonForNoMoneyOut'])]
    private ?string $reasonForNoMoneyOut = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getHas106flag(): bool
    {
        return $this->has106flag;
    }

    public function setHas106flag(bool $has106flag): static
    {
        $this->has106flag = $has106flag;

        return $this;
    }

    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTime $startDate): static
    {
        $startDate->setTime(0, 0);
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

    public function setDueDate(\DateTime $dueDate): static
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * Due date. By default, 8 weeks after the end date.
     */
    public function getDueDate(): \DateTime
    {
        return $this->dueDate;
    }

    /**
     * Days left to the due report
     * 0 = same day
     * -1 = overdue by 1 day
     * 1 = 1 day
     */
    public function getDueDateDiffDays(?\DateTime $currentDate = null): int
    {
        $currentDate = $currentDate ?: new \DateTime();

        // clone and set time to 0,0,0 (might not be needed)
        $currentDate = clone $currentDate;
        $currentDate->setTime(0, 0);
        $dueDate = clone $this->getDueDate();
        $dueDate->setTime(0, 0);

        return (int) $currentDate->diff($dueDate)->format('%R%a');
    }

    public function getSubmitDate(): ?\DateTime
    {
        return $this->submitDate;
    }

    public function setSubmitDate(?\DateTime $submitDate = null): static
    {
        $this->submitDate = $submitDate;

        return $this;
    }

    public function getUnSubmitDate(): ?\DateTime
    {
        return $this->unSubmitDate;
    }

    public function setUnSubmitDate(?\DateTime $unSubmitDate): static
    {
        $this->unSubmitDate = $unSubmitDate;

        return $this;
    }

    public function getSubmittedBy(): ?User
    {
        return $this->submittedBy;
    }

    public function setSubmittedBy(?User $submittedBy): static
    {
        $this->submittedBy = $submittedBy;

        return $this;
    }

    public function setEndDate(\DateTime $endDate): static
    {
        $this->endDate = $endDate->setTime(23, 59, 59);
        ;

        return $this;
    }

    /**
     * Generates next reporting period's start date.
     */
    public function getNextStartDate(): \DateTime
    {
        $reportingPeriodInDays = $this->calculateReportingPeriod();

        $nextStart = clone $this->getStartDate();
        $nextStart = $nextStart->modify('+ ' . (intval($reportingPeriodInDays) + 1) . ' days');
        $nextStart->setTime(0, 0);

        return $nextStart;
    }

    /**
     * Generates next reporting period's end date.
     * Note: Date diff returns 'difference' and so 1 day needs to be added.
     */
    public function getNextEndDate(): \DateTime
    {
        $reportingPeriodInDays = $this->calculateReportingPeriod();

        $nextEnd = clone $this->getEndDate();
        $nextEnd = $nextEnd->modify('+ ' . (intval($reportingPeriodInDays) + 1) . ' days');

        $nextEnd->setTime(0, 0);

        return $nextEnd;
    }

    /**
     * Calculates the Reporting period according to $format.
     *
     * @param string $format as recognised by \DateTime
     */
    private function calculateReportingPeriod(string $format = '%a'): string
    {
        return $this->getStartDate()->diff($this->getEndDate())->format($format);
    }

    /**
     * Return string representation of the start-end date period
     * e.g. '2004 to 2005'.
     *
     * NB this has the side effect of setting the period on the instance.
     */
    public function getPeriod(): string
    {
        if ($this->period !== null) {
            return $this->period;
        }

        $startDateStr = $this->startDate->format('Y');
        $endDateStr = $this->endDate->format('Y');

        if ($startDateStr !== $endDateStr) {
            $this->period = $startDateStr . ' to ' . $endDateStr;

            return $this->period;
        }

        $this->period = $startDateStr;

        return $this->period;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get contacts for the report sorted in ascending createdAt order.
     * Does not change the ordering of $this->contacts.
     *
     * @return array<Contact>
     */
    public function getContacts(): array
    {
        $contacts = [...$this->contacts];
        uasort($contacts, fn ($con1, $con2) => $con1->getCreatedAt() <=> $con2->getCreatedAt());
        return $contacts;
    }

    /**
     * @param array<Contact> $contacts
     */
    public function setContacts(array $contacts): static
    {
        $this->contacts = $contacts;

        return $this;
    }

    /**
     * Ordered by createdAt. Does not change the order of $this->decisions.
     *
     * @return array<Decision>
     */
    public function getDecisions(): array
    {
        $decisions = [...$this->decisions];
        uasort($decisions, fn ($dec1, $dec2) => $dec1->getCreatedAt() <=> $dec2->getCreatedAt());
        return $decisions;
    }

    /**
     * @param array<Decision> $decisions
     */
    public function setDecisions(array $decisions): static
    {
        $this->decisions = $decisions;

        return $this;
    }

    public function isDue(): ?bool
    {
        return $this->isDue;
    }

    public function hasContacts(): ?string
    {
        if (empty($this->getContacts()) && $this->getReasonForNoContacts() === null) {
            return null;
        }

        return $this->getReasonForNoContacts() ? 'no' : 'yes';
    }

    // necessary to simplify form logic
    public function setHasContacts(mixed $ignored): static
    {
        return $this;
    }

    public function getSignificantDecisionsMade(): ?string
    {
        return $this->significantDecisionsMade;
    }

    public function setSignificantDecisionsMade(?string $significantDecisionsMade): static
    {
        $this->significantDecisionsMade = $significantDecisionsMade;

        return $this;
    }

    // necessary to simplify form logic
    public function setHasDecisions(mixed $ignored): static
    {
        return $this;
    }

    public function setReasonForNoContacts(?string $reasonForNoContacts): static
    {
        $this->reasonForNoContacts = $reasonForNoContacts;

        return $this;
    }

    public function getReasonForNoContacts(): ?string
    {
        return $this->reasonForNoContacts;
    }

    public function setReasonForNoDecisions(?string $reasonForNoDecisions): static
    {
        $this->reasonForNoDecisions = $reasonForNoDecisions;

        return $this;
    }

    public function getReasonForNoDecisions(): ?string
    {
        return $this->reasonForNoDecisions;
    }

    public function getVisitsCare(): ?VisitsCare
    {
        return $this->visitsCare;
    }

    public function setVisitsCare(VisitsCare $visitsCare): void
    {
        $this->visitsCare = $visitsCare;
    }

    public function getLifestyle(): ?Lifestyle
    {
        return $this->lifestyle;
    }

    public function setLifestyle(Lifestyle $lifestyle): void
    {
        $this->lifestyle = $lifestyle;
    }

    public function getAction(): ?Action
    {
        return $this->action;
    }

    public function setAction(Action $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getMentalCapacity(): ?MentalCapacity
    {
        return $this->mentalCapacity;
    }

    public function setMentalCapacity(MentalCapacity $mentalCapacity)
    {
        $this->mentalCapacity = $mentalCapacity;

        return $this;
    }

    public function getNoAssetToAdd(): ?bool
    {
        return $this->noAssetToAdd;
    }

    public function setNoAssetToAdd(bool $noAssetToAdd): static
    {
        $this->noAssetToAdd = $noAssetToAdd;

        return $this;
    }

    public function getSubmitted(): ?bool
    {
        return $this->submitted;
    }

    public function setSubmitted(bool $submitted): static
    {
        $this->submitted = $submitted;

        return $this;
    }

    public function setReportSeen(bool $reportSeen): static
    {
        $this->reportSeen = $reportSeen;

        return $this;
    }

    public function getReportSeen(): ?bool
    {
        return $this->reportSeen;
    }

    public function isAgree(): ?bool
    {
        return $this->agree;
    }

    public function setAgree(?bool $agree): static
    {
        $this->agree = $agree;

        return $this;
    }

    public function getAgreedBehalfDeputy(): ?string
    {
        return $this->agreedBehalfDeputy;
    }

    public function setAgreedBehalfDeputy(?string $agreedBehalfDeputy)
    {
        $this->agreedBehalfDeputy = $agreedBehalfDeputy;

        return $this;
    }

    public function getAgreedBehalfDeputyExplanation(): ?string
    {
        return $this->agreedBehalfDeputyExplanation;
    }

    public function setAgreedBehalfDeputyExplanation(?string $agreedBehalfDeputyExplanation): static
    {
        $this->agreedBehalfDeputyExplanation = $agreedBehalfDeputyExplanation;

        return $this;
    }

    /**
     * @return array<Document>
     */
    public function getDocuments(): array
    {
        return $this->documents;
    }

    /**
     * @return array<Document>
     */
    public function getSubmittedDocuments(): array
    {
        return $this->submittedDocuments;
    }

    /**
     * @param array<Document> $submittedDocuments
     */
    public function setSubmittedDocuments(array $submittedDocuments): static
    {
        $this->submittedDocuments = $submittedDocuments;

        return $this;
    }

    /**
     * @return array<Document>
     */
    public function getUnsubmittedDocuments(): array
    {
        return $this->unsubmittedDocuments;
    }

    /**
     * Returns a list of deputy only documents. Those that should be visible to deputies only.
     * Excludes Report PDF and transactions PDF.
     *
     * @return array<Document>
     */
    public function getDeputyDocuments(): array
    {
        if (count($this->documents) > 0) {
            return array_filter($this->documents, function (Document $document): bool {
                return !($document->isAdminDocument() || $document->isReportPdf());
            });
        }

        return [];
    }

    /**
     * @param array<Document> $documents
     */
    public function setDocuments(array $documents): static
    {
        $this->documents = $documents;

        return $this;
    }

    public function getStatus(): Status
    {
        return $this->status ?: new Status($this);
    }

    public function setStatus(Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getWishToProvideDocumentation(): ?string
    {
        return $this->wishToProvideDocumentation;
    }

    public function setWishToProvideDocumentation(?string $wishToProvideDocumentation): static
    {
        $this->wishToProvideDocumentation = $wishToProvideDocumentation;

        return $this;
    }

    /**
     * @param string $format string where %s are endDate (Y), submitDate Y-m-d, case number
     */
    public function createAttachmentName(string $format): string
    {
        $submitDate = $this->getSubmitDate();

        return sprintf(
            $format,
            $this->getEndDate()->format('Y'),
            $submitDate instanceof \DateTime ?
                $submitDate->format('Y-m-d') : 'n-a-', // some old reports have no submission date
            $this->getClient()->getCaseNumber()
        );
    }

    /**
     * @return array<string>
     */
    public function getAvailableSections(): array
    {
        return $this->availableSections;
    }

    /**
     * @param array<string> $availableSections
     */
    public function setAvailableSections(array $availableSections): static
    {
        $this->availableSections = $availableSections;

        return $this;
    }

    public function hasSection(string $section): bool
    {
        return in_array($section, $this->getAvailableSections());
    }

    /**
     * Has this report been submitted?
     */
    public function isSubmitted(): bool
    {
        return $this->getSubmitted() === true;
    }

    /**
     * Generates the translation suffix to use depending on report type.
     *
     * 10x followed by "-104" for HW, "-4" for hybrid report and nothing for PF report
     */
    public function get104TransSuffix(): string
    {
        return (strpos($this->getType() ?? '', '-4') > 0) ?
            '-4' :
            (
                $this->getType() === '104' || $this->getType() === '104-6' ?
                '-104' : ''
            );
    }

    public function getChecklist(): ?Checklist
    {
        return $this->checklist;
    }

    public function setChecklist(?Checklist $checklist): static
    {
        $this->checklist = $checklist;

        return $this;
    }

    public function getReviewChecklist(): ?ReviewChecklist
    {
        return $this->reviewChecklist;
    }

    public function setReviewChecklist(?ReviewChecklist $reviewChecklist): static
    {
        $this->reviewChecklist = $reviewChecklist;

        return $this;
    }

    public function getPreviousReportData(): array
    {
        return $this->previousReportData;
    }

    public function setPreviousReportData(array $previousReportData): static
    {
        $this->previousReportData = $previousReportData;

        return $this;
    }

    public function isUnsubmitted(): bool
    {
        return $this->getUnSubmitDate() !== null && !$this->getSubmitted();
    }

    public function getReportTitle(): ?string
    {
        return $this->reportTitle;
    }

    public function setReportTitle(?string $reportTitle): static
    {
        $this->reportTitle = $reportTitle;

        return $this;
    }

    public function canLinkToBankAccounts(): bool
    {
        return in_array(
            $this->getType(),
            [
                Report::LAY_PFA_HIGH_ASSETS_TYPE,
                Report::LAY_COMBINED_HIGH_ASSETS_TYPE,
                Report::PROF_PFA_HIGH_ASSETS_TYPE,
                Report::PROF_COMBINED_HIGH_ASSETS_TYPE,
                Report::PA_PFA_HIGH_ASSETS_TYPE,
                Report::PA_COMBINED_HIGH_ASSETS_TYPE,
            ]
        );
    }

    public function isLayReport(): bool
    {
        return in_array(
            $this->getType(),
            [
                self::LAY_PFA_HIGH_ASSETS_TYPE,
                self::LAY_PFA_LOW_ASSETS_TYPE,
                self::LAY_HW_TYPE,
                self::LAY_COMBINED_HIGH_ASSETS_TYPE,
                self::LAY_COMBINED_LOW_ASSETS_TYPE
            ]
        );
    }

    public function isPAreport(): bool
    {
        return in_array(
            $this->getType(),
            [
                self::PA_PFA_HIGH_ASSETS_TYPE,
                self::PA_PFA_LOW_ASSETS_TYPE,
                self::PA_HW_TYPE,
                self::PA_COMBINED_HIGH_ASSETS_TYPE,
                self::PA_COMBINED_LOW_ASSETS_TYPE
            ]
        );
    }

    public function isProfReport(): bool
    {
        return in_array(
            $this->getType(),
            [
                self::PROF_PFA_HIGH_ASSETS_TYPE,
                self::PROF_PFA_LOW_ASSETS_TYPE,
                self::PROF_HW_TYPE,
                self::PROF_COMBINED_HIGH_ASSETS_TYPE,
                self::PROF_COMBINED_LOW_ASSETS_TYPE
            ]
        );
    }

    /**
     * @return array<ReportSubmission>
     */
    public function getReportSubmissions(): array
    {
        return $this->reportSubmissions;
    }

    /**
     * @param array<ReportSubmission> $reportSubmissions
     */
    public function setReportSubmissions(array $reportSubmissions): static
    {
        $this->reportSubmissions = $reportSubmissions;

        return $this;
    }

    public function determineReportType(): string
    {
        $type = $this->getType() ?? '';

        // Remove report type suffix if there is one.
        if (str_ends_with($type, '-5') || str_ends_with($type, '-6')) {
            $type = substr($type, 0, -2);
        }

        return match ($type) {
            self::TYPE_HEALTH_WELFARE => self::TYPE_ABBREVIATION_HW,
            self::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS, self::TYPE_PROPERTY_AND_AFFAIRS_LOW_ASSETS =>
                self::TYPE_ABBREVIATION_PF,
            default => self::TYPE_ABBREVIATION_COMBINED,
        };
    }

    public function getReportTypeDefinition(): string
    {
        return match ($this->determineReportType()) {
            self::TYPE_ABBREVIATION_HW => self::HEALTH_AND_WELFARE_REPORT,
            self::TYPE_ABBREVIATION_PF => self::PROPERTY_AND_AFFAIRS_REPORT,
            default => self::PROPERTY_AND_AFFAIRS_WITH_HEALTH_AND_WELFARE_REPORT,
        };
    }

    public function getClientBenefitsCheck(): ?ClientBenefitsCheck
    {
        return $this->clientBenefitsCheck;
    }

    public function setClientBenefitsCheck(?ClientBenefitsCheck $clientBenefitsCheck): static
    {
        $this->clientBenefitsCheck = $clientBenefitsCheck;

        return $this;
    }

    public function getMoneyInExists(): ?string
    {
        return $this->moneyInExists;
    }

    public function setMoneyInExists(?string $moneyInExists): static
    {
        $this->moneyInExists = $moneyInExists;

        return $this;
    }

    public function getReasonForNoMoneyIn(): ?string
    {
        return $this->reasonForNoMoneyIn;
    }

    public function setReasonForNoMoneyIn(?string $reasonForNoMoneyIn): static
    {
        $this->reasonForNoMoneyIn = $reasonForNoMoneyIn;

        return $this;
    }

    public function getMoneyOutExists(): ?string
    {
        return $this->moneyOutExists;
    }

    public function setMoneyOutExists(?string $moneyOutExists): static
    {
        $this->moneyOutExists = $moneyOutExists;

        return $this;
    }

    public function getReasonForNoMoneyOut(): ?string
    {
        return $this->reasonForNoMoneyOut;
    }

    public function setReasonForNoMoneyOut(?string $reasonForNoMoneyOut): static
    {
        $this->reasonForNoMoneyOut = $reasonForNoMoneyOut;

        return $this;
    }

    public function getPrimaryDeputy(): ?Deputy
    {
        return $this->primaryDeputy;
    }
}
