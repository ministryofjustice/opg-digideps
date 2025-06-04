<?php

namespace App\Entity\Report;

use App\Entity\Client;
use App\Entity\Ndr\Ndr;
use App\Entity\Report\Traits as ReportTraits;
use App\Entity\ReportInterface;
use App\Entity\Satisfaction;
use App\Entity\Traits\CreateUpdateTimestamps;
use App\Entity\User;
use App\Service\ReportService;
use App\Service\ReportStatusService;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use phpDocumentor\Reflection\Types\This;

/**
 * Reports.
 *
 * @ORM\Table(name="report",
 *     indexes={
 *
 *     @ORM\Index(name="end_date_idx", columns={"end_date"}),
 *     @ORM\Index(name="submit_date_idx", columns={"submit_date"}),
 *     @ORM\Index(name="submitted_idx", columns={"submitted"}),
 *     @ORM\Index(name="report_status_cached_idx", columns={"report_status_cached"})
 *  })
 *
 * @ORM\Entity(repositoryClass="App\Repository\ReportRepository")
 *
 * @ORM\HasLifecycleCallbacks()
 */
class Report implements ReportInterface
{
    use CreateUpdateTimestamps;
    use ReportTraits\AssetTrait;
    use ReportTraits\BankAccountTrait;
    use ReportTraits\BalanceTrait;
    use ReportTraits\ContactTrait;
    use ReportTraits\DecisionTrait;
    use ReportTraits\FeeExpensesTrait;
    use ReportTraits\GiftsTrait;
    use ReportTraits\MoneyShortTrait;
    use ReportTraits\MoneyTransactionTrait;
    use ReportTraits\MoneyTransferTrait;
    use ReportTraits\MoreInfoTrait;
    use ReportTraits\DebtTrait;
    use ReportTraits\ProfServiceFeesTrait;
    use ReportTraits\ReportProfDeputyCostsTrait;
    use ReportTraits\ReportProfDeputyCostsEstimateTrait;
    use ReportTraits\StatusTrait;

    /**
     * Reports with total amount of assets
     * Threshold under which reports should be 103, and not 102.
     */
    public const ASSETS_TOTAL_VALUE_103_THRESHOLD = 21000;

    public const HEALTH_WELFARE = 1;
    public const PROPERTY_AND_AFFAIRS = 2;

    public const STATUS_NOT_STARTED = 'notStarted';
    public const STATUS_READY_TO_SUBMIT = 'readyToSubmit';
    public const STATUS_NOT_FINISHED = 'notFinished';

    // https://opgtransform.atlassian.net/wiki/spaces/DEPDS/pages/135266255/Report+variations
    public const LAY_PFA_LOW_ASSETS_TYPE = '103';
    public const LAY_PFA_HIGH_ASSETS_TYPE = '102';
    public const LAY_HW_TYPE = '104';
    public const LAY_COMBINED_LOW_ASSETS_TYPE = '103-4';
    public const LAY_COMBINED_HIGH_ASSETS_TYPE = '102-4';

    // PA
    public const PA_PFA_LOW_ASSETS_TYPE = '103-6';
    public const PA_PFA_HIGH_ASSETS_TYPE = '102-6';
    public const PA_HW_TYPE = '104-6';
    public const PA_COMBINED_LOW_ASSETS_TYPE = '103-4-6';
    public const PA_COMBINED_HIGH_ASSETS_TYPE = '102-4-6';

    // PROF
    public const PROF_PFA_LOW_ASSETS_TYPE = '103-5';
    public const PROF_PFA_HIGH_ASSETS_TYPE = '102-5';
    public const PROF_HW_TYPE = '104-5';
    public const PROF_COMBINED_LOW_ASSETS_TYPE = '103-4-5';
    public const PROF_COMBINED_HIGH_ASSETS_TYPE = '102-4-5';

    public const TYPE_HEALTH_WELFARE = '104';
    public const TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS = '102';
    public const TYPE_PROPERTY_AND_AFFAIRS_LOW_ASSETS = '103';
    public const TYPE_COMBINED_HIGH_ASSETS = '102-4';
    public const TYPE_COMBINED_LOW_ASSETS = '103-4';

    public const ENABLE_FEE_SECTIONS = false;

    public const SECTION_DECISIONS = 'decisions';
    public const SECTION_CONTACTS = 'contacts';
    public const SECTION_VISITS_CARE = 'visitsCare';
    public const SECTION_LIFESTYLE = 'lifestyle';

    // money
    public const SECTION_BALANCE = 'balance'; // not a real section, but needed as a flag for the view and the validation
    public const SECTION_BANK_ACCOUNTS = 'bankAccounts';
    public const SECTION_MONEY_TRANSFERS = 'moneyTransfers';
    public const SECTION_MONEY_IN = 'moneyIn';
    public const SECTION_MONEY_OUT = 'moneyOut';
    public const SECTION_MONEY_IN_SHORT = 'moneyInShort';
    public const SECTION_MONEY_OUT_SHORT = 'moneyOutShort';
    public const SECTION_ASSETS = 'assets';
    public const SECTION_DEBTS = 'debts';
    public const SECTION_GIFTS = 'gifts';
    public const SECTION_CLIENT_BENEFITS_CHECK = 'clientBenefitsCheck';
    // end money

    public const SECTION_ACTIONS = 'actions';
    public const SECTION_OTHER_INFO = 'otherInfo';
    public const SECTION_DEPUTY_EXPENSES = 'deputyExpenses';

    // pa only
    public const SECTION_PA_DEPUTY_EXPENSES = 'paDeputyExpenses'; // 106, AKA Fee and expenses

    // prof only
    public const SECTION_PROF_CURRENT_FEES = 'profCurrentFees';
    public const SECTION_PROF_DEPUTY_COSTS = 'profDeputyCosts';
    public const SECTION_PROF_DEPUTY_COSTS_ESTIMATE = 'profDeputyCostsEstimate';

    public const SECTION_DOCUMENTS = 'documents';

    // Applies to both costs and estimate costs
    public const PROF_DEPUTY_COSTS_TYPE_FIXED = 'fixed';
    public const PROF_DEPUTY_COSTS_TYPE_ASSESSED = 'assessed';
    public const PROF_DEPUTY_COSTS_TYPE_BOTH = 'both';

    public const BENEFITS_CHECK_SECTION_REQUIRED_GRACE_PERIOD_DAYS = 60;

    // Decisions
    public const SIGNIFICANT_DECISION_MADE = 'Yes';
    public const SIGNIFICANT_DECISION_NOT_MADE = 'No';

    /**
     * https://opgtransform.atlassian.net/wiki/spaces/DEPDS/pages/135266255/Report+variations.
     *
     * @return array
     */
    public static function getSectionsSettings()
    {
        return [
            self::SECTION_DECISIONS => self::allRolesAllReportTypes(),
            self::SECTION_CONTACTS => self::allRolesAllReportTypes(),
            self::SECTION_VISITS_CARE => self::allRolesAllReportTypes(),
            self::SECTION_LIFESTYLE => self::allRolesHwAndCombinedReportTypes(),
            // money
            self::SECTION_BANK_ACCOUNTS => self::allRolesPfaAndCombinedReportTypes(),
            self::SECTION_MONEY_TRANSFERS => self::allRolesPfaAndCombinedHighAssetsReportTypes(),
            self::SECTION_MONEY_IN => self::allRolesPfaAndCombinedHighAssetsReportTypes(),
            self::SECTION_MONEY_OUT => self::allRolesPfaAndCombinedHighAssetsReportTypes(),
            self::SECTION_MONEY_IN_SHORT => self::allRolesPfaAndCombinedLowAssetsReportTypes(),
            self::SECTION_MONEY_OUT_SHORT => self::allRolesPfaAndCombinedLowAssetsReportTypes(),
            self::SECTION_ASSETS => self::allRolesPfaAndCombinedReportTypes(),
            self::SECTION_DEBTS => self::allRolesPfaAndCombinedReportTypes(),
            self::SECTION_GIFTS => self::allRolesPfaAndCombinedReportTypes(),
            self::SECTION_BALANCE => self::allRolesPfaAndCombinedHighAssetsReportTypes(),
            self::SECTION_CLIENT_BENEFITS_CHECK => self::allRolesPfaAndCombinedReportTypes(),
            // end money
            self::SECTION_ACTIONS => self::allRolesAllReportTypes(),
            self::SECTION_OTHER_INFO => self::allRolesAllReportTypes(),
            self::SECTION_DEPUTY_EXPENSES => self::layPfaAndCombinedReportTypes(),
            self::SECTION_PA_DEPUTY_EXPENSES => self::paPfaAndCombinedReportTypes(),
            self::SECTION_PROF_CURRENT_FEES => self::ENABLE_FEE_SECTIONS ? self::profPfaAndCombinedReportTypes() : [],
            self::SECTION_PROF_DEPUTY_COSTS => self::allProfReportTypes(),
            // add when ready
            self::SECTION_PROF_DEPUTY_COSTS_ESTIMATE => self::allProfReportTypes(),
            self::SECTION_DOCUMENTS => self::allRolesAllReportTypes(),
        ];
    }

    public static function getAllLayTypes(): array
    {
        return [
            self::LAY_PFA_LOW_ASSETS_TYPE,
            self::LAY_PFA_HIGH_ASSETS_TYPE,
            self::LAY_HW_TYPE,
            self::LAY_COMBINED_LOW_ASSETS_TYPE,
            self::LAY_COMBINED_HIGH_ASSETS_TYPE,
        ];
    }

    public static function getAllProfTypes(): array
    {
        return [
            self::PROF_PFA_LOW_ASSETS_TYPE,
            self::PROF_PFA_HIGH_ASSETS_TYPE,
            self::PROF_HW_TYPE,
            self::PROF_COMBINED_LOW_ASSETS_TYPE,
            self::PROF_COMBINED_HIGH_ASSETS_TYPE,
        ];
    }

    public static function getAllPaTypes(): array
    {
        return [
            self::PA_PFA_LOW_ASSETS_TYPE,
            self::PA_PFA_HIGH_ASSETS_TYPE,
            self::PA_HW_TYPE,
            self::PA_COMBINED_LOW_ASSETS_TYPE,
            self::PA_COMBINED_HIGH_ASSETS_TYPE,
        ];
    }

    /**
     * @var int
     *
     * @JMS\Groups({"report", "report-id"})
     *
     * @JMS\Type("integer")
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\SequenceGenerator(sequenceName="report_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string TYPE_ constants
     *
     * @JMS\Groups({"report", "report-type", "deputy-court-order-basic"})
     *
     * @JMS\Type("string")
     *
     * @ORM\Column(name="type", type="string", length=10, nullable=false)
     */
    private $type;

    /**
     * @var int
     *
     * @JMS\Groups({"report-client"})
     *
     * @JMS\Type("App\Entity\Client")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="reports")
     *
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $client;

    /**
     * @var VisitsCare
     *
     * @JMS\Groups({"visits-care"})
     *
     * @JMS\Type("App\Entity\Report\VisitsCare")
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Report\VisitsCare", mappedBy="report", cascade={"persist", "remove"}, fetch="LAZY")
     **/
    private $visitsCare;

    /**
     * @var Lifestyle
     *
     * @JMS\Groups({"lifestyle"})
     *
     * @JMS\Type("App\Entity\Report\Lifestyle")
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Report\Lifestyle", mappedBy="report", cascade={"persist", "remove"})
     **/
    private $lifestyle;

    /**
     * @var Action
     *
     * @JMS\Groups({"action"})
     *
     * @JMS\Type("App\Entity\Report\Action")
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Report\Action", mappedBy="report", cascade={"persist", "remove"})
     **/
    private $action;

    /**
     * @var MentalCapacity
     *
     * @JMS\Groups({ "mental-capacity"})
     *
     * @JMS\Type("App\Entity\Report\MentalCapacity")
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Report\MentalCapacity", mappedBy="report", cascade={"persist", "remove"})
     **/
    private $mentalCapacity;

    /**
     * @var ClientBenefitsCheck|null
     *
     * @JMS\Groups({"client-benefits-check"})
     *
     * @JMS\Type("App\Entity\Report\ClientBenefitsCheck")
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Report\ClientBenefitsCheck", mappedBy="report", cascade={"persist", "remove"})
     **/
    private $clientBenefitsCheck;

    /**
     * @var \DateTime
     *
     * @JMS\Groups({"report", "report-period"})
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @ORM\Column(name="start_date", type="date", nullable=true)
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @JMS\Groups({"report", "report-period"})
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @ORM\Column(name="due_date", type="date", nullable=true)
     */
    private $dueDate;

    /**
     * @var \DateTime
     *
     * @JMS\Groups({"report", "report-period"})
     *
     * @JMS\Accessor(getter="getEndDate")
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @ORM\Column(name="end_date", type="date", nullable=true)
     */
    private $endDate;

    /**
     * @var \DateTime
     *
     * @JMS\Groups({"report"})
     *
     * @JMS\Accessor(getter="getSubmitDate")
     *
     * @JMS\Type("DateTime")
     *
     * @ORM\Column(name="submit_date", type="datetime", nullable=true)
     */
    private $submitDate;

    /**
     * @var \DateTime
     *
     * @JMS\Accessor(getter="getUnSubmitDate")
     *
     * @JMS\Groups({"report"})
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @ORM\Column(name="un_submit_date", type="datetime", nullable=true)
     */
    private $unSubmitDate;

    /**
     * @var bool whether the report is submitted or not
     *
     * @JMS\Groups({"report"})
     *
     * @JMS\Type("boolean")
     *
     * @ORM\Column(name="submitted", type="boolean", nullable=true)
     */
    private $submitted;

    /**
     * @var User
     *
     * @JMS\Groups({"report-submitted-by"})
     *
     * @JMS\Type("App\Entity\User")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     *
     * @ORM\JoinColumn(name="submitted_by", referencedColumnName="id", onDelete="SET NULL")
     */
    private $submittedBy;

    /**
     * @deprecated client shouldn't need this anymore
     *
     * @var bool
     *
     * @JMS\Groups({"report"})
     *
     * @JMS\Type("boolean")
     *
     * @ORM\Column(name="report_seen", type="boolean", options={"default": true})
     */
    private $reportSeen;

    /**
     * @var string not_deputy|only_deputy|more_deputies_behalf|more_deputies_not_behalf
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report"})
     *
     * @ORM\Column(name="agreed_behalf_deputy", type="string", length=50, nullable=true)
     */
    private $agreedBehalfDeputy;

    /**
     * @var string required if agreedBehalfDeputy == more_deputies_not_behalf
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report"})
     *
     * @ORM\Column(name="agreed_behalf_deputy_explanation", type="text", nullable=true)
     */
    private $agreedBehalfDeputyExplanation;

    /**
     * @var ArrayCollection
     *
     * @JMS\Type("ArrayCollection<App\Entity\Report\Document>")
     *
     * @JMS\Groups({"report-documents"})
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Report\Document", mappedBy="report", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     *
     * @ORM\OrderBy({"createdOn"="DESC"})
     */
    private $documents;

    /**
     * @JMS\Type("ArrayCollection<App\Entity\Report\ReportSubmission>")
     *
     * @JMS\Groups({"document-sync"})
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Report\ReportSubmission", mappedBy="report", fetch="EXTRA_LAZY")
     */
    private $reportSubmissions;

    /**
     * @var string
     *
     * @JMS\Groups({"report", "wish-to-provide-documentation"})
     *
     * @JMS\Type("string")
     *
     * @ORM\Column(name="wish_to_provide_documentation", type="string", nullable=true)
     */
    private $wishToProvideDocumentation;

    /**
     * @var string yes/no
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report", "current-prof-payments-received"})
     *
     * @ORM\Column(name="current_prof_payments_received", type="string", nullable=true)
     */
    private $currentProfPaymentsReceived;

    /**
     * @var string yes/no
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report", "report-prof-estimate-fees"})
     *
     * @ORM\Column(name="previous_prof_fees_estimate_given", length=3, type="string", nullable=true)
     */
    private $previousProfFeesEstimateGiven;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report", "report-prof-estimate-fees"})
     *
     * @ORM\Column(name="prof_fees_estimate_scco_reason", type="text", nullable=true)
     */
    private $profFeesEstimateSccoReason;

    /**
     * @var string yes | no (see constants)
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report", "significantDecisionsMade"})
     *
     * @ORM\Column(name="significant_decisions_made", type="text", nullable=true)
     */
    private $significantDecisionsMade;

    /**
     * @JMS\Groups({"report"})
     *
     * @ORM\Column(name="unsubmitted_sections_list", type="text", nullable=true)
     *
     * @JMS\Type("string")
     */
    private ?string $unsubmittedSectionsList;

    /**
     * @var Checklist
     *
     * @JMS\Groups({"report", "report-checklist"})
     *
     * @JMS\Type("App\Entity\Report\Checklist")
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Report\Checklist", mappedBy="report", cascade={"persist", "remove"})
     */
    private $checklist;

    /**
     * @var ReviewChecklist
     *
     * @JMS\Groups({"report", "report-checklist"})
     *
     * @JMS\Type("App\Entity\Report\ReviewChecklist")
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Report\ReviewChecklist", mappedBy="report", cascade={"persist", "remove"})
     */
    private $reviewChecklist;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Satisfaction", mappedBy="report", cascade={"persist", "remove"})
     *
     * @JMS\Type("App\Entity\Satisfaction")
     *
     * @JMS\Groups({"user-research", "satisfaction"})
     */
    private Satisfaction $satisfaction;

    /**
     * @var string yes | no
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report", "doesMoneyInExist"})
     *
     * @ORM\Column(name="money_in_exists", type="text", nullable=true)
     */
    private $moneyInExists;

    /**
     * @var string captures reason for no money in. Required if no money has gone in
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report", "reasonForNoMoneyIn"})
     *
     * @ORM\Column(name="reason_for_no_money_in", type="text", nullable=true)
     */
    private $reasonForNoMoneyIn;

    /**
     * @var string yes | no
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report", "doesMoneyOutExist"})
     *
     * @ORM\Column(name="money_out_exists", type="text", nullable=true)
     */
    private $moneyOutExists;

    /**
     * @var string captures reason for no money out. Required if no money has gone out
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report", "reasonForNoMoneyOut"})
     *
     * @ORM\Column(name="reason_for_no_money_out", type="text", nullable=true)
     **/
    private $reasonForNoMoneyOut;

    /**
     * @JMS\Groups({"report-with-court-orders"})
     *
     * @JMS\Type("ArrayCollection<App\Entity\CourtOrder>")
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\CourtOrder", mappedBy="reports", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $courtOrders;

    private array $excludeSections = [];
    private ?\DateTime $benefitsSectionReleaseDate = null;

    /**
     * Report constructor.
     *
     * @param bool $dateChecks if true, perform checks around multiple reports and dates. Useful for PA upload
     */
    public function __construct(Client $client, $type, \DateTime $startDate, \DateTime $endDate, $dateChecks = true)
    {
        if (!in_array($type, self::allRolesAllReportTypes())) {
            throw new \InvalidArgumentException("$type not a valid report type");
        }
        $this->type = $type;
        $this->client = $client;
        $this->startDate = new \DateTime($startDate->format('Y-m-d'), new \DateTimeZone('Europe/London'));
        $this->endDate = new \DateTime($endDate->format('Y-m-d'), new \DateTimeZone('Europe/London'));
        $this->updateDueDateBasedOnEndDate();

        // check date interval overlapping other reports
        if ($dateChecks && count($client->getSubmittedReports())) {
            $unsubmittedEndDates = array_map(function ($report) {
                return $report->getEndDate();
            }, $client->getSubmittedReports()->toArray());
            rsort($unsubmittedEndDates); // order by last first
            $endDateLastReport = $unsubmittedEndDates[0];
            $expectedStartDate = clone $endDateLastReport;
            $expectedStartDate->modify('+1 day');
            $daysDiff = (int) $expectedStartDate->diff($this->startDate)->format('%a');
            if (0 !== $daysDiff) {
                throw new \RuntimeException(sprintf('Incorrect start date. Last submitted report was on %s, therefore the new report is expected to start on %s, not on %s', $endDateLastReport->format('d/m/Y'), $expectedStartDate->format('d/m/Y'), $this->startDate->format('d/m/Y')));
            }
        }

        $this->contacts = new ArrayCollection();
        $this->bankAccounts = new ArrayCollection();
        $this->moneyTransfers = new ArrayCollection();
        $this->moneyTransactions = new ArrayCollection();
        $this->moneyShortCategories = new ArrayCollection();
        $this->moneyTransactionsShort = new ArrayCollection();
        $this->debts = new ArrayCollection();
        $this->fees = new ArrayCollection();
        $this->decisions = new ArrayCollection();
        $this->assets = new ArrayCollection();
        $this->noAssetToAdd = null;
        $this->noTransfersToAdd = null;
        $this->reportSeen = true;
        $this->expenses = new ArrayCollection();
        $this->gifts = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->reportSubmissions = new ArrayCollection();
        $this->wishToProvideDocumentation = null;
        $this->currentProfPaymentsReceived = null;
        $this->profServiceFees = new ArrayCollection();
        $this->checklist = null;
        $this->profDeputyPreviousCosts = new ArrayCollection();
        $this->profDeputyInterimCosts = new ArrayCollection();
        $this->profDeputyEstimateCosts = new ArrayCollection();

        // set sections as notStarted when a new report is created
        $statusCached = [];
        foreach ($this->getAvailableSections() as $sectionId) {
            $statusCached[$sectionId] = ['state' => ReportStatusService::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }

        $this->setSectionStatusesCached($statusCached);
        $this->reportStatusCached = self::STATUS_NOT_STARTED;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type see TYPE_ constants
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * set Due date to +21 days after end date (Lay reports) if end date before 13/11/19 otherwise +56 days.
     */
    public function updateDueDateBasedOnEndDate()
    {
        // due date set to 8 weeks (56 days) after the end date unless lay reports where end date is beyond
        // 13/11/19. Then it is 21 days (DDPB-2996)
        $this->dueDate = clone $this->endDate;
        if ($this->isLayReport() && $this->getEndDate()->format('Ymd') >= '20191113') {
            $this->dueDate->add(new \DateInterval('P21D'));
        } else {
            $this->dueDate->add(new \DateInterval('P56D'));
        }
    }

    /**
     * Get sections based on the report type.
     *
     * @JMS\VirtualProperty
     *
     * @JMS\Groups({"report", "report-sections"})
     *
     * @JMS\Type("array")
     */
    public function getAvailableSections()
    {
        if (!$this->requiresBenefitsCheckSection()) {
            $this->excludeSections = [Report::SECTION_CLIENT_BENEFITS_CHECK];
        } else {
            $this->excludeSections = [];
        }

        $ret = [];
        foreach (self::getSectionsSettings() as $sectionId => $reportTypes) {
            if (in_array($sectionId, $this->excludeSections)) {
                continue;
            }

            if (in_array($this->getType(), $reportTypes)) {
                $ret[] = $sectionId;
            }
        }

        return $ret;
    }

    /**
     * @param string $section see SECTION_ constants
     *
     * @return bool
     */
    public function hasSection($section)
    {
        return in_array($section, $this->getAvailableSections());
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
     * Set startDate.
     *
     * @return Report
     */
    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate.
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate.
     *
     * @return Report
     */
    public function setEndDate(\DateTime $endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate.
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * For check reasons.
     *
     * @return string
     */
    public function hasSamePeriodAs(Report $report)
    {
        return $this->startDate->format('Ymd') === $report->getStartDate()->format('Ymd')
            && $this->endDate->format('Ymd') === $report->getEndDate()->format('Ymd');
    }

    /**
     * Set submitDate.
     *
     * @return Report
     */
    public function setSubmitDate(?\DateTime $submitDate = null)
    {
        $this->submitDate = $submitDate;

        return $this;
    }

    /**
     * Get submitDate.
     *
     * @return \DateTime
     */
    public function getSubmitDate()
    {
        return $this->submitDate;
    }

    /**
     * @return \DateTime|null
     */
    public function getUnSubmitDate()
    {
        return $this->unSubmitDate;
    }

    /**
     * @param \DateTime|null $unSubmitDate
     *
     * @return Report
     */
    public function setUnSubmitDate($unSubmitDate)
    {
        $this->unSubmitDate = $unSubmitDate;

        return $this;
    }

    /**
     * Set submitted.
     *
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
     * Get submitted.
     *
     * @return bool
     */
    public function getSubmitted()
    {
        return $this->submitted;
    }

    public function getSubmittedBy()
    {
        return $this->submittedBy;
    }

    /**
     * @return Report
     */
    public function setSubmittedBy($submittedBy)
    {
        $this->submittedBy = $submittedBy;

        return $this;
    }

    /**
     * Set client.
     *
     * @return Report
     */
    public function setClient(?Client $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client.
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    public function getClientId()
    {
        return $this->client->getId();
    }

    /**
     * @return VisitsCare
     */
    public function getVisitsCare()
    {
        return $this->visitsCare;
    }

    /**
     * @return Report
     */
    public function setVisitsCare(?VisitsCare $visitsCare = null)
    {
        $this->visitsCare = $visitsCare;

        return $this;
    }

    /**
     * @return Lifestyle
     */
    public function getLifestyle()
    {
        return $this->lifestyle;
    }

    /**
     * @param Lifestyle $lifestyle
     */
    public function setLifestyle($lifestyle)
    {
        $this->lifestyle = $lifestyle;
    }

    /**
     * @return Action
     */
    public function getAction()
    {
        return $this->action;
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
     * Set reportSeen.
     *
     * @param bool $reportSeen
     *
     * @return Report
     */
    public function setReportSeen($reportSeen)
    {
        $this->reportSeen = $reportSeen;

        return $this;
    }

    /**
     * Get reportSeen.
     *
     * @return bool
     */
    public function getReportSeen()
    {
        return $this->reportSeen;
    }

    /**
     * @return bool
     */
    public function belongsToUser(User $user)
    {
        return in_array($user->getId(), $this->getClient()->getUserIds());
    }

    public function getAgreedBehalfDeputy()
    {
        return $this->agreedBehalfDeputy;
    }

    public function setAgreedBehalfDeputy($agreeBehalfDeputy)
    {
        $acceptedValues = ['not_deputy', 'only_deputy', 'more_deputies_behalf', 'more_deputies_not_behalf'];

        if ($agreeBehalfDeputy && !in_array($agreeBehalfDeputy, $acceptedValues)) {
            throw new \InvalidArgumentException(__METHOD__." {$agreeBehalfDeputy} given. Expected value: ".implode(' or ', $acceptedValues));
        }

        $this->agreedBehalfDeputy = $agreeBehalfDeputy;

        return $this;
    }

    public function getAgreedBehalfDeputyExplanation()
    {
        return $this->agreedBehalfDeputyExplanation;
    }

    public function setAgreedBehalfDeputyExplanation($agreedBehalfDeputyExplanation)
    {
        $this->agreedBehalfDeputyExplanation = $agreedBehalfDeputyExplanation;

        return $this;
    }

    /**
     * @JMS\VirtualProperty
     *
     * @JMS\SerializedName("is_due")
     *
     * @JMS\Groups({"report"})
     */
    public function isDue()
    {
        return ReportService::isDue($this->getEndDate());
    }

    /**
     * @return Report
     */
    public function setDueDate(\DateTime $dueDate)
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     ** @return bool
     */
    public function isMissingMoneyOrAccountsOrClosingBalance()
    {
        return !$this->hasAccounts()
        || count($this->getBankAccountsIncomplete()) > 0;
    }

    /**
     * Temporary until specs gets more clear around report types
     * This value could be set at creation time if needed in the future.
     * Until now, 106 is for all the PAs, so we get this value from the (only) user accessing the report.
     * Not sure if convenient to implement a 106 separate report, as 106 is also both an 102 AND an 103.
     *
     * if it has the 106 flag, the deputy expense section is replaced with a more detailed "PA deputy expense" section
     * //TODO remove from mocks
     *
     * @JMS\VirtualProperty
     *
     * @JMS\Type("boolean")
     *
     * @JMS\SerializedName("has106flag")
     *
     * @JMS\Groups({"report", "report-106-flag"})
     */
    public function has106Flag()
    {
        return '-6' === substr($this->type, -2);
    }

    /**
     * @return ArrayCollection|Document[]
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Unsubmitted Reports.
     *
     * @JMS\VirtualProperty
     *
     * @JMS\SerializedName("unsubmitted_documents")
     *
     * @JMS\Groups({"documents"})
     *
     * @return ArrayCollection|Document[]
     */
    public function getUnsubmittedDocuments()
    {
        return $this->getDeputyDocuments()->filter(function ($d) {
            return empty($d->getReportSubmission());
        });
    }

    /**
     * Submitted reports.
     *
     * @JMS\VirtualProperty("submittedDocuments")
     *
     * @JMS\SerializedName("submitted_documents")
     *
     * @JMS\Groups({"documents"})
     *
     * @JMS\Type("ArrayCollection<App\Entity\Report\Document>")
     *
     * @return ArrayCollection|Document[]
     */
    public function getSubmittedDocuments()
    {
        return $this->getDeputyDocuments()->filter(function ($d) {
            return !empty($d->getReportSubmission());
        });
    }

    public function addDocument(Document $document)
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
        }
    }

    /**
     * @JMS\SerializedName("report_submissions")
     */
    public function getReportSubmissions()
    {
        return $this->reportSubmissions;
    }

    /**
     * @return Report
     */
    public function addReportSubmission(ReportSubmission $submission)
    {
        if (!$this->reportSubmissions->contains($submission)) {
            $this->reportSubmissions->add($submission);
        }
    }

    /**
     * @return string|null
     */
    public function getWishToProvideDocumentation()
    {
        return $this->wishToProvideDocumentation;
    }

    /**
     * @return $this
     */
    public function setWishToProvideDocumentation($wishToProvideDocumentation)
    {
        $this->wishToProvideDocumentation = $wishToProvideDocumentation;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentProfPaymentsReceived()
    {
        return $this->currentProfPaymentsReceived;
    }

    /**
     * @param string $currentProfPaymentsReceived
     */
    public function setCurrentProfPaymentsReceived($currentProfPaymentsReceived)
    {
        $this->currentProfPaymentsReceived = $currentProfPaymentsReceived;
    }

    /**
     * @return string
     */
    public function getPreviousProfFeesEstimateGiven()
    {
        return $this->previousProfFeesEstimateGiven;
    }

    /**
     * @param string $previousProfFeesEstimateGiven
     *
     * @return $this
     */
    public function setPreviousProfFeesEstimateGiven($previousProfFeesEstimateGiven)
    {
        $this->previousProfFeesEstimateGiven = $previousProfFeesEstimateGiven;

        return $this;
    }

    /**
     * @return string
     */
    public function getProfFeesEstimateSccoReason()
    {
        return $this->profFeesEstimateSccoReason;
    }

    /**
     * @param string $profFeesEstimateSccoReason
     *
     * @return $this
     */
    public function setProfFeesEstimateSccoReason($profFeesEstimateSccoReason)
    {
        $this->profFeesEstimateSccoReason = $profFeesEstimateSccoReason;

        return $this;
    }

    public function getSignificantDecisionsMade(): ?string
    {
        return $this->significantDecisionsMade;
    }

    public function setSignificantDecisionsMade(?string $significantDecisionsMade): self
    {
        $this->significantDecisionsMade = $significantDecisionsMade;

        return $this;
    }

    public function getUnsubmittedSectionsList(): ?string
    {
        return $this->unsubmittedSectionsList;
    }

    public function setUnsubmittedSectionsList(?string $unsubmittedSectionsList): void
    {
        $this->unsubmittedSectionsList = $unsubmittedSectionsList;
    }

    /**
     * Returns a list of deputy only documents. Those that should be visible to deputies only.
     * Excludes Report PDF and transactions PDF.
     *
     * @return Document[]
     */
    public function getDeputyDocuments()
    {
        return $this->getDocuments()->filter(function ($document) {
            /* @var $document Document */
            return !($document->isAdminDocument() || $document->isReportPdf());
        });
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
     */
    public function setChecklist($checklist)
    {
        $this->checklist = $checklist;
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
     * Previous report data. Just return id and type for second api call to allo new JMS groups.
     *
     * @JMS\VirtualProperty
     *
     * @JMS\SerializedName("previous_report_data")
     *
     * @JMS\Groups({"previous-report-data"})
     *
     * @JMS\Type("array")
     *
     * @return array
     */
    public function getPreviousReportData()
    {
        $previousReport = $this->getPreviousReport();

        if (empty($previousReport)) {
            return [];
        }

        return [
            'report-summary' => $previousReport->getReportSummary(),
            'financial-summary' => $previousReport->getFinancialSummary(),
        ];
    }

    /**
     * Method to identify and return previous report.
     *
     * @return Ndr|Report|bool|mixed
     */
    private function getPreviousReport()
    {
        $clientReports = $this->getClient()->getReports();

        // ensure order is correct most recent first
        $iterator = $clientReports->getIterator();
        $iterator->uasort(function ($a, $b) {
            return ($a->getId() > $b->getId()) ? -1 : 1;
        });
        $orderedClientReports = new ArrayCollection(iterator_to_array($iterator));

        // try previous reports
        foreach ($orderedClientReports as $clientReport) {
            if ($clientReport->getId() < $this->getId()) {
                // less than should imply their previous report
                return $clientReport;
            }
        }

        // try NDR
        /** @var Ndr $ndr */
        $ndr = $this->getClient()->getNdr();

        if (!empty($ndr)) {
            return $ndr;
        }

        return false;
    }

    /**
     * Report financial summary, contains bank accounts and balance information.
     *
     * @return array
     */
    public function getFinancialSummary()
    {
        $accounts = [];
        $openingBalanceTotal = 0;
        /** @var BankAccount $ba */
        foreach ($this->getBankAccounts() as $ba) {
            $accounts[$ba->getId()]['nameOneLine'] = $ba->getNameOneLine();
            $accounts[$ba->getId()]['bank'] = $ba->getBank();
            $accounts[$ba->getId()]['accountType'] = $ba->getAccountTypeText();
            $accounts[$ba->getId()]['openingBalance'] = $ba->getOpeningBalance();
            $accounts[$ba->getId()]['closingBalance'] = $ba->getClosingBalance();
            $accounts[$ba->getId()]['isClosed'] = $ba->getIsClosed();
            $accounts[$ba->getId()]['isJointAccount'] = $ba->getIsJointAccount();

            $openingBalanceTotal += $ba->getOpeningBalance();
        }

        return [
            'accounts' => $accounts,
            'opening-balance-total' => $openingBalanceTotal,
            'closing-balance-total' => $this->getAccountsClosingBalanceTotal(),
        ];
    }

    /**
     * Report summary, contains basic information about a report. Called via report.previousReportData so as not to
     * return everything.
     *
     * @return array
     */
    public function getReportSummary()
    {
        return [
            'type' => $this->getType(),
        ];
    }

    /**
     * Returns the translation key relating to the type of report. Hybrids identified to determine any suffix required
     * for the translation keys (translations are in 'report' domain).
     *
     * @JMS\VirtualProperty
     *
     * @JMS\Groups({"report"})
     *
     * @JMS\Type("string")
     *
     * @return string
     */
    public function getReportTitle()
    {
        $titleTranslationKeys = [
            self::LAY_PFA_LOW_ASSETS_TYPE => 'propertyAffairsMinimal',
            self::LAY_PFA_HIGH_ASSETS_TYPE => 'propertyAffairsGeneral',
            self::LAY_HW_TYPE => 'healthWelfare',
            self::LAY_COMBINED_LOW_ASSETS_TYPE => 'propertyAffairsMinimalHealthWelfare',
            self::LAY_COMBINED_HIGH_ASSETS_TYPE => 'propertyAffairsGeneralHealthWelfare',

            self::PA_PFA_LOW_ASSETS_TYPE => 'propertyAffairsMinimal',
            self::PA_PFA_HIGH_ASSETS_TYPE => 'propertyAffairsGeneral',
            self::PA_HW_TYPE => 'healthWelfare',
            self::PA_COMBINED_LOW_ASSETS_TYPE => 'propertyAffairsMinimalHealthWelfare',
            self::PA_COMBINED_HIGH_ASSETS_TYPE => 'propertyAffairsGeneralHealthWelfare',

            self::PROF_PFA_LOW_ASSETS_TYPE => 'propertyAffairsMinimal',
            self::PROF_PFA_HIGH_ASSETS_TYPE => 'propertyAffairsGeneral',
            self::PROF_HW_TYPE => 'healthWelfare',
            self::PROF_COMBINED_LOW_ASSETS_TYPE => 'propertyAffairsMinimalHealthWelfare',
            self::PROF_COMBINED_HIGH_ASSETS_TYPE => 'propertyAffairsGeneralHealthWelfare',
        ];

        return $titleTranslationKeys[$this->getType()];
    }

    /**
     * @return bool true if report is lay type, otherwise false
     */
    public function isLayReport()
    {
        return in_array($this->getType(), [self::LAY_PFA_HIGH_ASSETS_TYPE, self::LAY_PFA_LOW_ASSETS_TYPE, self::LAY_HW_TYPE, self::LAY_COMBINED_HIGH_ASSETS_TYPE, self::LAY_COMBINED_LOW_ASSETS_TYPE]);
    }

    public function isPAreport()
    {
        return in_array($this->getType(), [self::PA_PFA_HIGH_ASSETS_TYPE, self::PA_PFA_LOW_ASSETS_TYPE, self::PA_HW_TYPE, self::PA_COMBINED_HIGH_ASSETS_TYPE, self::PA_COMBINED_LOW_ASSETS_TYPE]);
    }

    public function isProfReport()
    {
        return in_array($this->getType(), [self::PROF_PFA_HIGH_ASSETS_TYPE, self::PROF_PFA_LOW_ASSETS_TYPE, self::PROF_HW_TYPE, self::PROF_COMBINED_HIGH_ASSETS_TYPE, self::PROF_COMBINED_LOW_ASSETS_TYPE]);
    }

    public function getSatisfaction(): Satisfaction
    {
        return $this->satisfaction;
    }

    public function setSatisfaction(Satisfaction $satisfaction): Report
    {
        $this->satisfaction = $satisfaction;

        return $this;
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

    /**
     * The client benefits check section of the report should be required for:.
     *
     * Reports with an unsubmit date that had originally completed the section
     * Reports without an unsubmit date and a due date more than 60 days after the client benefits section release date
     */
    public function requiresBenefitsCheckSection(): bool
    {
        if ($this->getUnSubmitDate()) {
            return $this->getClientBenefitsCheck() instanceof ClientBenefitsCheck;
        } else {
            // Provides a positive or negative string showing days between feature flag and due date
            $diffInDays = $this->getBenefitsSectionReleaseDate()->diff($this->getDueDate())->format('%R%a');

            return intval($diffInDays) > self::BENEFITS_CHECK_SECTION_REQUIRED_GRACE_PERIOD_DAYS;
        }
    }

    public function getExcludeSections(): array
    {
        return $this->excludeSections;
    }

    public function setExcludeSections(array $excludeSections): Report
    {
        $this->excludeSections = $excludeSections;

        return $this;
    }

    public function getBenefitsSectionReleaseDate(): ?\DateTime
    {
        return $this->benefitsSectionReleaseDate ?: new \DateTime('16-03-2022 00:00:00');
    }

    public function setBenefitsSectionReleaseDate(?\DateTime $benefitsSectionReleaseDate): Report
    {
        $this->benefitsSectionReleaseDate = $benefitsSectionReleaseDate;

        return $this;
    }

    public static function allRolesAllReportTypes(): array
    {
        return [
            self::LAY_PFA_LOW_ASSETS_TYPE, self::LAY_PFA_HIGH_ASSETS_TYPE, self::LAY_HW_TYPE, self::LAY_COMBINED_LOW_ASSETS_TYPE, self::LAY_COMBINED_HIGH_ASSETS_TYPE,
            self::PA_PFA_LOW_ASSETS_TYPE, self::PA_PFA_HIGH_ASSETS_TYPE, self::PA_HW_TYPE, self::PA_COMBINED_LOW_ASSETS_TYPE, self::PA_COMBINED_HIGH_ASSETS_TYPE,
            self::PROF_PFA_LOW_ASSETS_TYPE, self::PROF_PFA_HIGH_ASSETS_TYPE, self::PROF_HW_TYPE, self::PROF_COMBINED_LOW_ASSETS_TYPE, self::PROF_COMBINED_HIGH_ASSETS_TYPE,
        ];
    }

    public static function allRolesHwAndCombinedReportTypes(): array
    {
        return [
            self::LAY_HW_TYPE, self::LAY_COMBINED_LOW_ASSETS_TYPE, self::LAY_COMBINED_HIGH_ASSETS_TYPE,
            self::PA_HW_TYPE, self::PA_COMBINED_LOW_ASSETS_TYPE, self::PA_COMBINED_HIGH_ASSETS_TYPE,
            self::PROF_HW_TYPE, self::PROF_COMBINED_LOW_ASSETS_TYPE, self::PROF_COMBINED_HIGH_ASSETS_TYPE,
        ];
    }

    public static function allRolesPfaAndCombinedReportTypes(): array
    {
        return [
            self::LAY_PFA_LOW_ASSETS_TYPE, self::LAY_PFA_HIGH_ASSETS_TYPE, self::LAY_COMBINED_LOW_ASSETS_TYPE, self::LAY_COMBINED_HIGH_ASSETS_TYPE,
            self::PA_PFA_LOW_ASSETS_TYPE, self::PA_PFA_HIGH_ASSETS_TYPE, self::PA_COMBINED_LOW_ASSETS_TYPE, self::PA_COMBINED_HIGH_ASSETS_TYPE,
            self::PROF_PFA_LOW_ASSETS_TYPE, self::PROF_PFA_HIGH_ASSETS_TYPE, self::PROF_COMBINED_LOW_ASSETS_TYPE, self::PROF_COMBINED_HIGH_ASSETS_TYPE,
        ];
    }

    public static function allProfReportTypes(): array
    {
        return [
            self::PROF_PFA_LOW_ASSETS_TYPE, self::PROF_PFA_HIGH_ASSETS_TYPE, self::PROF_HW_TYPE, self::PROF_COMBINED_LOW_ASSETS_TYPE, self::PROF_COMBINED_HIGH_ASSETS_TYPE,
        ];
    }

    public static function allRolesPfaAndCombinedHighAssetsReportTypes(): array
    {
        return [
            self::LAY_PFA_HIGH_ASSETS_TYPE, self::LAY_COMBINED_HIGH_ASSETS_TYPE,
            self::PA_PFA_HIGH_ASSETS_TYPE, self::PA_COMBINED_HIGH_ASSETS_TYPE,
            self::PROF_PFA_HIGH_ASSETS_TYPE, self::PROF_COMBINED_HIGH_ASSETS_TYPE,
        ];
    }

    public static function allRolesPfaAndCombinedLowAssetsReportTypes(): array
    {
        return [
            self::LAY_PFA_LOW_ASSETS_TYPE, self::LAY_COMBINED_LOW_ASSETS_TYPE,
            self::PA_PFA_LOW_ASSETS_TYPE, self::PA_COMBINED_LOW_ASSETS_TYPE,
            self::PROF_PFA_LOW_ASSETS_TYPE, self::PROF_COMBINED_LOW_ASSETS_TYPE,
        ];
    }

    public static function layPfaAndCombinedReportTypes(): array
    {
        return [
            self::LAY_PFA_LOW_ASSETS_TYPE, self::LAY_PFA_HIGH_ASSETS_TYPE, self::LAY_COMBINED_LOW_ASSETS_TYPE, self::LAY_COMBINED_HIGH_ASSETS_TYPE,
        ];
    }

    public static function paPfaAndCombinedReportTypes(): array
    {
        return [
            self::PA_PFA_LOW_ASSETS_TYPE, self::PA_PFA_HIGH_ASSETS_TYPE, self::PA_COMBINED_LOW_ASSETS_TYPE, self::PA_COMBINED_HIGH_ASSETS_TYPE,
        ];
    }

    public static function profPfaAndCombinedReportTypes(): array
    {
        return [
            self::PROF_PFA_LOW_ASSETS_TYPE, self::PROF_PFA_HIGH_ASSETS_TYPE, self::PROF_COMBINED_LOW_ASSETS_TYPE, self::PROF_COMBINED_HIGH_ASSETS_TYPE,
        ];
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
