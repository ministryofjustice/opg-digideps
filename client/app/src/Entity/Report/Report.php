<?php

namespace App\Entity\Report;

use App\Entity\Client;
use App\Entity\Report\Traits as ReportTraits;
use App\Entity\ReportInterface;
use App\Entity\User;
use App\Validator\Constraints as AppAssert;
use App\Validator\Constraints\StartEndDateComparableInterface;
use DateTime;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AppAssert\EndDateNotGreaterThanFifteenMonths(groups={"start-end-dates"})
 *
 * @AppAssert\EndDateNotBeforeStartDate(groups={"start-end-dates"})
 *
 * @AppAssert\YearMustBeFourDigits(groups={"start-end-dates"})
 *
 * @AppAssert\ProfDeputyCostsEstimate\CostBreakdownNotGreaterThanTotal(groups={"prof-deputy-estimate-costs"})
 *
 * @Assert\Callback(callback="debtsValid", groups={"debts"})
 * @Assert\Callback(callback="feesValid", groups={"fees"})
 * @Assert\Callback(callback="profCostsInterimAtLeastOne", groups={"prof-deputy-interim-costs"})
 * @Assert\Callback(callback="unsubmittedSectionAtLeastOnce", groups={"unsubmitted_sections"})
 */
class Report implements ReportInterface, StartEndDateComparableInterface
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

    // Applies to both costs and estimate costs
    public const PROF_DEPUTY_COSTS_TYPE_FIXED = 'fixed';
    public const PROF_DEPUTY_COSTS_TYPE_ASSESSED = 'assessed';
    public const PROF_DEPUTY_COSTS_TYPE_BOTH = 'both';

    public const STATUS_NOT_STARTED = 'notStarted';
    public const STATUS_READY_TO_SUBMIT = 'readyToSubmit';
    public const STATUS_NOT_FINISHED = 'notFinished';

    public const TYPE_HEALTH_WELFARE = '104';
    public const TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS = '102';
    public const TYPE_PROPERTY_AND_AFFAIRS_LOW_ASSETS = '103';
    public const TYPE_COMBINED_HIGH_ASSETS = '102-4';
    public const TYPE_COMBINED_LOW_ASSETS = '103-4';

    public const TYPE_ABBREVIATION_HW = 'HW';
    public const TYPE_ABBREVIATION_PF = 'PF';
    public const TYPE_ABBREVIATION_COMBINED = 'COMBINED';

    public const HIGH_ASSETS_REPORT_TYPES = [
        self::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS,
        self::TYPE_COMBINED_HIGH_ASSETS,
    ];

    public const BENEFITS_CHECK_SECTION_REQUIRED_GRACE_PERIOD_DAYS = 60;

    // Decisions
    public const SIGNIFICANT_DECISION_MADE = 'Yes';
    public const SIGNIFICANT_DECISION_NOT_MADE = 'No';

    // Money in and out exists
    public const YES_MONEY_EXISTS = 'Yes';
    public const NO_MONEY_EXISTS = 'No';

    /**
     * @JMS\Type("integer")
     *
     * @JMS\Groups({"visits-care", "report-id"})
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report_type"})
     *
     * see TYPE_* constant
     *
     * @var string
     */
    private $type;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $has106flag;

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @JMS\Groups({"startEndDates"})
     *
     * @Assert\NotBlank( message="report.startDate.notBlank", groups={"start-end-dates"} )
     *
     * @Assert\Type(type="DateTimeInterface", message="report.startDate.invalidMessage", groups={"start-end-dates"} )
     *
     * @var \DateTime|null
     */
    private $startDate;

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @JMS\Groups({"startEndDates"})
     *
     * @Assert\NotBlank( message="report.endDate.notBlank", groups={"start-end-dates"} )
     *
     * @Assert\Type(type="DateTimeInterface", message="report.endDate.invalidMessage", groups={"start-end-dates"} )
     *
     * @var \DateTime|null
     */
    private $endDate;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $isDue;

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @JMS\Groups({"report_due_date"})
     *
     * @var \DateTime|null
     */
    private $dueDate;

    /**
     * @var \DateTime|null
     *
     * @JMS\Type("DateTime")
     *
     * @JMS\Groups({"submit"})
     */
    private $submitDate;

    /**
     * @var \DateTime|null
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @JMS\Groups({"unsubmit_date"})
     */
    private $unSubmitDate;

    /**
     * @JMS\Type("App\Entity\User")
     *
     * @var User
     */
    private $submittedBy;

    /**
     * @JMS\Type("array<App\Entity\Report\ReportSubmission>")
     *
     * @var ReportSubmission[]
     */
    private $reportSubmissions;

    /**
     * @JMS\Type("App\Entity\Client")
     *
     * @var Client
     */
    private $client;

    /**
     * @JMS\Exclude
     *
     * @var string
     */
    private $period;

    /**
     * @JMS\Type("array<App\Entity\Report\Contact>")
     *
     * @var Contact[]
     */
    private $contacts = [];

    /**
     * @JMS\Type("array<App\Entity\Report\Decision>")
     *
     * @var Decision[]
     */
    private $decisions = [];

    /**
     * @JMS\Type("App\Entity\Report\VisitsCare")
     *
     * @var VisitsCare|null
     */
    private $visitsCare;

    /**
     * @JMS\Type("App\Entity\Report\Lifestyle")
     *
     * @var Lifestyle|null
     */
    private $lifestyle;

    /**
     * @JMS\Type("App\Entity\Report\Action")
     *
     * @var Action|null
     */
    private $action;

    /**
     * @JMS\Type("App\Entity\Report\MentalCapacity")
     *
     * @var MentalCapacity
     */
    private $mentalCapacity;

    /**
     * @JMS\Type("App\Entity\Report\ClientBenefitsCheck")
     *
     * @Assert\Valid(groups={"client-benefits-check"})
     *
     * @JMS\Groups({"client-benefits-check"})
     *
     * @var ClientBenefitsCheck
     */
    private $clientBenefitsCheck;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"reasonForNoContacts"})
     *
     * @Assert\NotBlank( message="contact.reasonForNoContacts.notBlank", groups={"reasonForNoContacts"})
     *
     * @var string|null
     */
    private $reasonForNoContacts;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report","significantDecisionsMade"})
     */
    private $significantDecisionsMade;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"reasonForNoDecisions"})
     *
     * @Assert\NotBlank( message="decision.reasonForNoDecisions.notBlank", groups={"reason-no-decisions"})
     *
     * @var string|null
     */
    private $reasonForNoDecisions;

    /**
     * @JMS\Type("boolean")
     *
     * @JMS\Groups({"noAssetsToAdd"})
     *
     * @var bool
     */
    private $noAssetToAdd;

    /**
     * @JMS\Type("boolean")
     *
     * @JMS\Groups({"submit", "submitted"})
     *
     * @var bool
     */
    private $submitted;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $reportSeen;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     *
     * @Assert\IsTrue(message="report.agree", groups={"declare"} )
     */
    private $agree;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report","submit", "submit_agreed"})
     *
     * @Assert\NotBlank(message="report.agreedBehalfDeputy.notBlank", groups={"declare"} )
     */
    private $agreedBehalfDeputy;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report","submit", "submit_agreed"})
     *
     * @Assert\NotBlank(message="report.agreedBehalfDeputyExplanation.notBlank", groups={"declare-explanation"} )
     */
    private $agreedBehalfDeputyExplanation;

    /**
     * @var Document[]
     *
     * @JMS\Groups({"report-documents"})
     *
     * @JMS\Type("array<App\Entity\Report\Document>")
     */
    private $documents = [];

    /**
     * @JMS\Type("array<App\Entity\Report\Document>")
     *
     * @JMS\Groups({"report-documents"})
     *
     * @var Document[]
     */
    private $submittedDocuments = [];

    /**
     * @JMS\Type("array<App\Entity\Report\Document>")
     *
     * @JMS\Groups({"report-documents"})
     *
     * @var Document[]
     */
    private $unsubmittedDocuments = [];

    /**
     * @JMS\Type("App\Entity\Report\Status")
     *
     * @var Status
     */
    private $status;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report", "wish-to-provide-documentation", "report-documents"})
     *
     * @Assert\NotBlank(message="document.wishToProvideDocumentation.notBlank", groups={"wish-to-provide-documentation"})
     */
    private $wishToProvideDocumentation;

    /**
     * @var array
     *
     * @JMS\Type("array")
     */
    private $availableSections = [];

    /**
     * @var Checklist
     *
     * @JMS\Type("App\Entity\Report\Checklist")
     **/
    private $checklist;

    /**
     * @var ReviewChecklist
     *
     * @JMS\Type("App\Entity\Report\ReviewChecklist")
     **/
    private $reviewChecklist;

    /**
     * @var array
     *
     * @JMS\Type("array")
     **/
    private $previousReportData = [];

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $reportTitle;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report", "doesMoneyInExist"})
     *
     * @Assert\NotBlank( message="moneyIn.moneyInChoice.notBlank", groups={"doesMoneyInExist"})
     */
    private $moneyInExists;

    /**
     * @var string captures reason for no money in. Required if no money has gone in
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report", "reasonForNoMoneyIn"})
     *
     * @Assert\NotBlank( message="moneyIn.reasonForNoMoneyIn.notBlank", groups={"reasonForNoMoneyIn"})
     *
     * @var string|null
     */
    private $reasonForNoMoneyIn;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report", "doesMoneyOutExist"})
     *
     * @Assert\NotBlank( message="moneyOut.moneyOutChoice.notBlank", groups={"doesMoneyOutExist"})
     */
    private $moneyOutExists;

    /**
     * @var string captures reason for no money out. Required if no money has gone out
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report", "reasonForNoMoneyOut"})
     *
     * @Assert\NotBlank( message="moneyOut.reasonForNoMoneyOut.notBlank", groups={"reasonForNoMoneyOut"})
     */
    private $reasonForNoMoneyOut;

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
     * @return Report
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return bool
     */
    public function getHas106flag()
    {
        return $this->has106flag;
    }

    /**
     * @param bool $has106flag
     */
    public function setHas106flag($has106flag)
    {
        $this->has106flag = $has106flag;
    }

    /**
     * @return \DateTime|null
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @return Report
     */
    public function setStartDate(?\DateTime $startDate = null)
    {
        if ($startDate instanceof \DateTime) {
            $startDate->setTime(0, 0, 0);
        }
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return \DateTime|null $endDate
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    public function setDueDate(?\DateTime $dueDate = null): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * Due date.
     *
     * as a default, 8 weeks after the end date
     *
     * @return \DateTime|null $dueDate
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * Returns the days left to the due report
     * 0 = same day
     * -1 = overdue by 1 day
     * 1 = 1 day.
     *
     * @return int|void
     */
    public function getDueDateDiffDays(?\DateTime $currentDate = null)
    {
        if (is_null($this->getDueDate())) {
            return;
        }

        $currentDate = $currentDate ? $currentDate : new \DateTime();

        // clone and set time to 0,0,0 (might not be needed)
        $currentDate = clone $currentDate;
        $currentDate->setTime(0, 0, 0);
        $dueDate = clone $this->getDueDate();
        $dueDate->setTime(0, 0, 0);

        $days = (int) $currentDate->diff($dueDate)->format('%R%a');

        return $days;
    }

    /**
     * Get submitDate.
     *
     * @return \DateTime|null
     */
    public function getSubmitDate()
    {
        return $this->submitDate;
    }

    /**
     * @return Report
     */
    public function setSubmitDate(?\DateTime $submitDate = null)
    {
        $this->submitDate = $submitDate;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUnSubmitDate()
    {
        return $this->unSubmitDate;
    }

    /**
     * @return Report
     */
    public function setUnSubmitDate(?\DateTime $unSubmitDate)
    {
        $this->unSubmitDate = $unSubmitDate;

        return $this;
    }

    /**
     * @return User
     */
    public function getSubmittedBy()
    {
        return $this->submittedBy;
    }

    public function setSubmittedBy(?User $submittedBy): self
    {
        $this->submittedBy = $submittedBy;

        return $this;
    }

    /**
     * @return Report
     */
    public function setEndDate(?\DateTime $endDate = null)
    {
        if ($endDate instanceof \DateTime) {
            $endDate->setTime(23, 59, 59);
        }
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Generates next reporting period's start date.
     *
     * @return \DateTime|null
     */
    public function getNextStartDate()
    {
        if (is_null($this->getStartDate())) {
            return null;
        }

        $reportingPeriodInDays = $this->calculateReportingPeriod('%a');
        if (!empty($reportingPeriodInDays)) {
            $nextStart = clone $this->getStartDate();
            $nextStart = $nextStart->modify('+ '.(intval($reportingPeriodInDays) + 1).' days');
            $nextStart->setTime(0, 0, 0);

            return $nextStart;
        }

        return null;
    }

    /**
     * Generates next reporting period's end date.
     * Note: Date diff returns 'difference' and so 1 day needs to be added.
     *
     * @return \DateTime|null
     */
    public function getNextEndDate()
    {
        if (is_null($this->getEndDate())) {
            return null;
        }

        $reportingPeriodInDays = $this->calculateReportingPeriod('%a');
        if (!empty($reportingPeriodInDays)) {
            $nextEnd = clone $this->getEndDate();
            $nextEnd = $nextEnd->modify('+ '.(intval($reportingPeriodInDays) + 1).' days');

            $nextEnd->setTime(0, 0, 0);

            return $nextEnd;
        }

        return null;
    }

    /**
     * Calculates the Reporting period according to $format.
     *
     * @param string $format recognised by \DateTime
     *
     * @return string|null
     */
    private function calculateReportingPeriod($format = '%a')
    {
        if ($this->getStartDate() instanceof \DateTime && $this->getEndDate() instanceof \DateTime) {
            // add one day because difference doesn't include end date itself
            return $this->getStartDate()->diff($this->getEndDate())->format($format);
        }

        return null;
    }

    /**
     * Return string representation of the start-end date period
     * e.g. 2004 to 2005.
     *
     * @return string $period
     */
    public function getPeriod()
    {
        if ($this->period) {
            return $this->period;
        }

        if (!$this->startDate instanceof \DateTime || !$this->endDate instanceof \DateTime) {
            return $this->period;
        }

        $startDateStr = $this->startDate->format('Y');
        $endDateStr = $this->endDate->format('Y');

        if ($startDateStr != $endDateStr) {
            $this->period = $startDateStr.' to '.$endDateStr;

            return $this->period;
        }
        $this->period = $startDateStr;

        return $this->period;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return Report
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return array $contacts
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @param array $contacts
     *
     * @return $this
     */
    public function setContacts($contacts)
    {
        $this->contacts = $contacts;

        return $this;
    }

    /**
     * @var array
     */
    public function getDecisions()
    {
        return $this->decisions;
    }

    /**
     * @param Decision[] $decisions
     *
     * @return Report
     */
    public function setDecisions($decisions)
    {
        $this->decisions = $decisions;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDue()
    {
        return $this->isDue;
    }

    public function hasContacts()
    {
        if (empty($this->getContacts()) && null === $this->getReasonForNoContacts()) {
            return null;
        }

        return $this->getReasonForNoContacts() ? 'no' : 'yes';
    }

    public function setHasContacts($value)
    {
        // necessary to simplify form logic
        return null;
    }

    public function getSignificantDecisionsMade()
    {
        return $this->significantDecisionsMade;
    }

    public function setSignificantDecisionsMade($significantDecisionsMade)
    {
        $this->significantDecisionsMade = $significantDecisionsMade;

        return $this;
    }

    public function setHasDecisions($value)
    {
        // necessary to simplify form logic
        return null;
    }

    /**
     * @param string $reasonForNoContacts
     *
     * @return Report
     */
    public function setReasonForNoContacts($reasonForNoContacts)
    {
        $this->reasonForNoContacts = $reasonForNoContacts;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getReasonForNoContacts()
    {
        return $this->reasonForNoContacts;
    }

    /**
     * @param string $reasonForNoDecisions
     *
     * @return Report
     */
    public function setReasonForNoDecisions($reasonForNoDecisions)
    {
        $this->reasonForNoDecisions = $reasonForNoDecisions;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getReasonForNoDecisions()
    {
        return $this->reasonForNoDecisions;
    }

    /**
     * @return VisitsCare
     */
    public function getVisitsCare()
    {
        return $this->visitsCare ?: new VisitsCare();
    }

    /**
     * @param VisitsCare $visitsCare
     */
    public function setVisitsCare($visitsCare)
    {
        $this->visitsCare = $visitsCare;
    }

    /**
     * @return Lifestyle
     */
    public function getLifestyle()
    {
        return $this->lifestyle ?: new Lifestyle();
    }

    /**
     * @param Lifestyle $lifestyle
     */
    public function setLifestyle($lifestyle)
    {
        $this->lifestyle = $lifestyle;
    }

    public function getAction()
    {
        return $this->action ?: new Action();
    }

    /**
     * @return Report
     */
    public function setAction(Action $action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return MentalCapacity
     */
    public function getMentalCapacity()
    {
        return $this->mentalCapacity;
    }

    public function setMentalCapacity(MentalCapacity $mentalCapacity)
    {
        $this->mentalCapacity = $mentalCapacity;

        return $this;
    }

    /**
     * @return bool $noAssetToAdd
     */
    public function getNoAssetToAdd()
    {
        return $this->noAssetToAdd;
    }

    /**
     * @param bool $noAssetToAdd
     *
     * @return Report
     */
    public function setNoAssetToAdd($noAssetToAdd)
    {
        $this->noAssetToAdd = $noAssetToAdd;

        return $this;
    }

    /**
     * @return bool $submitted
     */
    public function getSubmitted()
    {
        return $this->submitted;
    }

    /**
     * @param bool $submitted
     *
     * @return Report
     */
    public function setSubmitted($submitted)
    {
        $this->submitted = $submitted;

        return $this;
    }

    /**
     * @param bool $reportSeen
     *
     * @return Report
     */
    public function setReportSeen($reportSeen)
    {
        $this->reportSeen = $reportSeen;
    }

    /**
     * @return bool
     */
    public function getReportSeen()
    {
        return $this->reportSeen;
    }

    /**
     * @return bool
     */
    public function isAgree()
    {
        return $this->agree;
    }

    /**
     * @param bool $agree
     */
    public function setAgree($agree)
    {
        $this->agree = $agree;
    }

    /**
     * @return string
     */
    public function getAgreedBehalfDeputy()
    {
        return $this->agreedBehalfDeputy;
    }

    /**
     * @param string $agreedBehalfDeputy
     */
    public function setAgreedBehalfDeputy($agreedBehalfDeputy)
    {
        $this->agreedBehalfDeputy = $agreedBehalfDeputy;

        return $this;
    }

    /**
     * @return string
     */
    public function getAgreedBehalfDeputyExplanation()
    {
        return $this->agreedBehalfDeputyExplanation;
    }

    /**
     * @param string $agreedBehalfDeputyExplanation
     */
    public function setAgreedBehalfDeputyExplanation($agreedBehalfDeputyExplanation)
    {
        $this->agreedBehalfDeputyExplanation = $agreedBehalfDeputyExplanation;

        return $this;
    }

    /**
     * @return Document[]
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @return Document[]
     */
    public function getSubmittedDocuments()
    {
        return $this->submittedDocuments;
    }

    /**
     * @param Document[] $submittedDocuments
     */
    public function setSubmittedDocuments(array $submittedDocuments): self
    {
        $this->submittedDocuments = $submittedDocuments;

        return $this;
    }

    /**
     * @return Document[]
     */
    public function getUnsubmittedDocuments()
    {
        return $this->unsubmittedDocuments;
    }

    /**
     * Returns a list of deputy only documents. Those that should be visible to deputies only.
     * Excludes Report PDF and transactions PDF.
     *
     * @return Document[]
     */
    public function getDeputyDocuments()
    {
        if (is_array($this->documents)) {
            return array_filter($this->documents, function ($document) {
                /* @var $document Document */
                return !($document->isAdminDocument() || $document->isReportPdf());
            });
        }

        return [];
    }

    /**
     * @param Document[] $documents
     */
    public function setDocuments($documents)
    {
        $this->documents = $documents;

        return $this;
    }

    /**
     * @return Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param Status $status $statusrvice
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getWishToProvideDocumentation()
    {
        return $this->wishToProvideDocumentation;
    }

    /**
     * @param string $wishToProvideDocumentation
     *
     * @return $this
     */
    public function setWishToProvideDocumentation($wishToProvideDocumentation)
    {
        $this->wishToProvideDocumentation = $wishToProvideDocumentation;

        return $this;
    }

    /**
     * @param string $format string where %s are endDate (Y), submitDate Y-m-d, case number
     *
     * @return string
     */
    public function createAttachmentName($format)
    {
        $endDate = $this->getEndDate();
        $submitDate = $this->getSubmitDate();

        if (is_null($endDate)) {
            throw new \RuntimeException('Cannot create an attachment for a report with no end date');
        }

        $attachmentName = sprintf(
            $format,
            $endDate->format('Y'),
            $submitDate instanceof \DateTime ? $submitDate->format('Y-m-d') : 'n-a-', // some old reports have no submission date
            $this->getClient()->getCaseNumber()
        );

        return $attachmentName;
    }

    /**
     * @return array
     */
    public function getAvailableSections()
    {
        return $this->availableSections;
    }

    /**
     * @param array $availableSections
     *
     * @return Report
     */
    public function setAvailableSections($availableSections)
    {
        $this->availableSections = $availableSections;

        return $this;
    }

    /**
     * @param string $section
     *
     * @return bool
     */
    public function hasSection($section)
    {
        return in_array($section, $this->getAvailableSections());
    }

    /**
     * Has this report been submitted?
     *
     * @return bool
     */
    public function isSubmitted()
    {
        return (bool) $this->getSubmitted();
    }

    /**
     * Generates the translation suffic to use depending on report type,.
     *
     * 10x followed by "-104" for HW, "-4" for hybrid report and nothing for PF report
     *
     * @return string
     */
    public function get104TransSuffix()
    {
        return (strpos($this->getType(), '-4') > 0) ?
            '-4' :
            (
                '104' === $this->getType() || '104-6' === $this->getType() ?
                '-104' : ''
            );
    }

    /**
     * @return Checklist
     */
    public function getChecklist()
    {
        return $this->checklist;
    }

    /**
     * @param Checklist $checklist
     *
     * @return $this
     */
    public function setChecklist($checklist)
    {
        $this->checklist = $checklist;

        return $this;
    }

    /**
     * @return ReviewChecklist
     */
    public function getReviewChecklist()
    {
        return $this->reviewChecklist;
    }

    /**
     * @param ReviewChecklist $reviewChecklist
     */
    public function setReviewChecklist($reviewChecklist)
    {
        $this->reviewChecklist = $reviewChecklist;
    }

    /**
     * @return array
     */
    public function getPreviousReportData()
    {
        return $this->previousReportData;
    }

    /**
     * @param array $previousReportData
     *
     * @return $this
     */
    public function setPreviousReportData($previousReportData)
    {
        $this->previousReportData = $previousReportData;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUnsubmitted()
    {
        return $this->getUnSubmitDate() && !$this->getSubmitted();
    }

    /**
     * @return string
     */
    public function getReportTitle()
    {
        return $this->reportTitle;
    }

    /**
     * @param string $reportTitle
     *
     * @return $this
     */
    public function setReportTitle($reportTitle)
    {
        $this->reportTitle = $reportTitle;

        return $this;
    }

    /**
     * @return bool
     */
    public function canLinkToBankAccounts()
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
        return in_array($this->getType(), [self::LAY_PFA_HIGH_ASSETS_TYPE, self::LAY_PFA_LOW_ASSETS_TYPE, self::LAY_HW_TYPE, self::LAY_COMBINED_HIGH_ASSETS_TYPE, self::LAY_COMBINED_LOW_ASSETS_TYPE]);
    }

    public function isPAreport(): bool
    {
        return in_array($this->getType(), [self::PA_PFA_HIGH_ASSETS_TYPE, self::PA_PFA_LOW_ASSETS_TYPE, self::PA_HW_TYPE, self::PA_COMBINED_HIGH_ASSETS_TYPE, self::PA_COMBINED_LOW_ASSETS_TYPE]);
    }

    public function isProfReport(): bool
    {
        return in_array($this->getType(), [self::PROF_PFA_HIGH_ASSETS_TYPE, self::PROF_PFA_LOW_ASSETS_TYPE, self::PROF_HW_TYPE, self::PROF_COMBINED_HIGH_ASSETS_TYPE, self::PROF_COMBINED_LOW_ASSETS_TYPE]);
    }

    /**
     * @return ReportSubmission[]
     */
    public function getReportSubmissions(): array
    {
        return $this->reportSubmissions;
    }

    /**
     * @param ReportSubmission[] $reportSubmissions
     */
    public function setReportSubmissions(array $reportSubmissions): self
    {
        $this->reportSubmissions = $reportSubmissions;

        return $this;
    }

    public function determineReportType()
    {
        switch ($this->getType()) {
            case self::TYPE_HEALTH_WELFARE:
                return self::TYPE_ABBREVIATION_HW;
            case self::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS:
            case self::TYPE_PROPERTY_AND_AFFAIRS_LOW_ASSETS:
                return self::TYPE_ABBREVIATION_PF;
            default:
                return self::TYPE_ABBREVIATION_COMBINED;
        }
    }

    public function getClientBenefitsCheck(): ?ClientBenefitsCheck
    {
        return $this->clientBenefitsCheck;
    }

    public function setClientBenefitsCheck(?ClientBenefitsCheck $clientBenefitsCheck): Report
    {
        $this->clientBenefitsCheck = $clientBenefitsCheck;

        return $this;
    }

    public function getMoneyInExists(): ?string
    {
        return $this->moneyInExists;
    }

    public function setMoneyInExists(?string $moneyInExists): self
    {
        $this->moneyInExists = $moneyInExists;

        return $this;
    }

    public function getReasonForNoMoneyIn(): ?string
    {
        return $this->reasonForNoMoneyIn;
    }

    public function setReasonForNoMoneyIn(?string $reasonForNoMoneyIn): self
    {
        $this->reasonForNoMoneyIn = $reasonForNoMoneyIn;

        return $this;
    }

    public function getMoneyOutExists(): ?string
    {
        return $this->moneyOutExists;
    }

    public function setMoneyOutExists(?string $moneyOutExists): self
    {
        $this->moneyOutExists = $moneyOutExists;

        return $this;
    }

    public function getReasonForNoMoneyOut(): ?string
    {
        return $this->reasonForNoMoneyOut;
    }

    public function setReasonForNoMoneyOut(?string $reasonForNoMoneyOut): self
    {
        $this->reasonForNoMoneyOut = $reasonForNoMoneyOut;

        return $this;
    }
}
