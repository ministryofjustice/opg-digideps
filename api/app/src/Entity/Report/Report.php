<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Report\Traits\AssetTrait;
use OPG\Digideps\Backend\Entity\Report\Traits\BalanceTrait;
use OPG\Digideps\Backend\Entity\Report\Traits\BankAccountTrait;
use OPG\Digideps\Backend\Entity\Report\Traits\ContactTrait;
use OPG\Digideps\Backend\Entity\Report\Traits\DebtTrait;
use OPG\Digideps\Backend\Entity\Report\Traits\DecisionTrait;
use OPG\Digideps\Backend\Entity\Report\Traits\FeeExpensesTrait;
use OPG\Digideps\Backend\Entity\Report\Traits\GiftsTrait;
use OPG\Digideps\Backend\Entity\Report\Traits\MoneyShortTrait;
use OPG\Digideps\Backend\Entity\Report\Traits\MoneyTransactionTrait;
use OPG\Digideps\Backend\Entity\Report\Traits\MoneyTransferTrait;
use OPG\Digideps\Backend\Entity\Report\Traits\MoreInfoTrait;
use OPG\Digideps\Backend\Entity\Report\Traits\ReportProfDeputyCostsEstimateTrait;
use OPG\Digideps\Backend\Entity\Report\Traits\ReportProfDeputyCostsTrait;
use OPG\Digideps\Backend\Entity\Report\Traits\StatusTrait;
use OPG\Digideps\Backend\Entity\Satisfaction;
use OPG\Digideps\Backend\Entity\Traits\CreateUpdateTimestamps;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Repository\ReportRepository;
use OPG\Digideps\Backend\Service\ReportService;
use OPG\Digideps\Backend\Service\ReportStatusService;

#[ORM\Table(name: 'report')]
#[ORM\Index(columns: ['end_date'], name: 'end_date_idx')]
#[ORM\Index(columns: ['submit_date'], name: 'submit_date_idx')]
#[ORM\Index(columns: ['submitted'], name: 'submitted_idx')]
#[ORM\Index(columns: ['report_status_cached'], name: 'report_status_cached_idx')]
#[ORM\Entity(repositoryClass: ReportRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Report
{
    use CreateUpdateTimestamps;
    use AssetTrait;
    use BankAccountTrait;
    use BalanceTrait;
    use ContactTrait;
    use DecisionTrait;
    use FeeExpensesTrait;
    use GiftsTrait;
    use MoneyShortTrait;
    use MoneyTransactionTrait;
    use MoneyTransferTrait;
    use MoreInfoTrait;
    use DebtTrait;
    use ReportProfDeputyCostsTrait;
    use ReportProfDeputyCostsEstimateTrait;
    use StatusTrait;

    /**
     * Reports with total amount of assets
     * Threshold under which reports should be 103, and not 102.
     */
    public const int ASSETS_TOTAL_VALUE_103_THRESHOLD = 21000;

    public const string STATUS_NOT_STARTED = 'notStarted';
    public const string STATUS_READY_TO_SUBMIT = 'readyToSubmit';
    public const string STATUS_NOT_FINISHED = 'notFinished';

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

    public const string TYPE_HEALTH_WELFARE = '104';
    public const string TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS = '102';
    public const string TYPE_PROPERTY_AND_AFFAIRS_LOW_ASSETS = '103';
    public const string TYPE_COMBINED_HIGH_ASSETS = '102-4';
    public const string TYPE_COMBINED_LOW_ASSETS = '103-4';

    public const string SECTION_DECISIONS = 'decisions';
    public const string SECTION_CONTACTS = 'contacts';
    public const string SECTION_VISITS_CARE = 'visitsCare';
    public const string SECTION_LIFESTYLE = 'lifestyle';

    // money
    public const string SECTION_BALANCE = 'balance'; // not a real section, but needed as a flag for the view and the validation
    public const string SECTION_BANK_ACCOUNTS = 'bankAccounts';
    public const string SECTION_MONEY_TRANSFERS = 'moneyTransfers';
    public const string SECTION_MONEY_IN = 'moneyIn';
    public const string SECTION_MONEY_OUT = 'moneyOut';
    public const string SECTION_MONEY_IN_SHORT = 'moneyInShort';
    public const string SECTION_MONEY_OUT_SHORT = 'moneyOutShort';
    public const string SECTION_ASSETS = 'assets';
    public const string SECTION_DEBTS = 'debts';
    public const string SECTION_GIFTS = 'gifts';
    public const string SECTION_CLIENT_BENEFITS_CHECK = 'clientBenefitsCheck';
    // end money

    public const string SECTION_ACTIONS = 'actions';
    public const string SECTION_OTHER_INFO = 'otherInfo';
    public const string SECTION_DEPUTY_EXPENSES = 'deputyExpenses';

    // pa only
    public const string SECTION_PA_DEPUTY_EXPENSES = 'paDeputyExpenses'; // 106, AKA Fee and expenses

    // prof only
    public const string SECTION_PROF_CURRENT_FEES = 'profCurrentFees';
    public const string SECTION_PROF_DEPUTY_COSTS = 'profDeputyCosts';
    public const string SECTION_PROF_DEPUTY_COSTS_ESTIMATE = 'profDeputyCostsEstimate';

    public const string SECTION_DOCUMENTS = 'documents';

    // Applies to both costs and estimate costs
    public const string PROF_DEPUTY_COSTS_TYPE_FIXED = 'fixed';

    public const int BENEFITS_CHECK_SECTION_REQUIRED_GRACE_PERIOD_DAYS = 60;

    // Decisions
    public const string SIGNIFICANT_DECISION_MADE = 'Yes';
    public const string SIGNIFICANT_DECISION_NOT_MADE = 'No';

    /**
     * https://opgtransform.atlassian.net/wiki/spaces/DEPDS/pages/135266255/Report+variations.
     *
     * @return array<string, array<string>>
     */
    public static function getSectionsSettings(): array
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
            self::SECTION_PROF_DEPUTY_COSTS => self::allProfReportTypes(),
            // add when ready
            self::SECTION_PROF_DEPUTY_COSTS_ESTIMATE => self::allProfReportTypes(),
            self::SECTION_DOCUMENTS => self::allRolesAllReportTypes(),
        ];
    }

    /**
     * @return array<string>
     */
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

    /**
     * @return array<string>
     */
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

    /**
     * @return array<string>
     */
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

    #[JMS\Groups(['report', 'report-id'])]
    #[JMS\Type('integer')]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'report_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    /**
     * See TYPE_ constants
     */
    #[JMS\Groups(['report', 'report-type', 'deputy-court-order-basic'])]
    #[JMS\Type('string')]
    #[ORM\Column(name: 'type', type: 'string', length: 10, nullable: false)]
    private string $type;

    #[JMS\Groups(['report-client'])]
    #[JMS\Type('OPG\Digideps\Backend\Entity\Client')]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Client::class, cascade: ['persist'], inversedBy: 'reports')]
    private Client $client;

    #[JMS\Groups(['visits-care'])]
    #[JMS\Type('OPG\Digideps\Backend\Entity\Report\VisitsCare')]
    #[ORM\OneToOne(mappedBy: 'report', targetEntity: VisitsCare::class, cascade: ['persist', 'remove'], fetch: 'LAZY')]
    private ?VisitsCare $visitsCare = null;

    #[JMS\Groups(['lifestyle'])]
    #[JMS\Type('OPG\Digideps\Backend\Entity\Report\Lifestyle')]
    #[ORM\OneToOne(mappedBy: 'report', targetEntity: Lifestyle::class, cascade: ['persist', 'remove'])]
    private ?Lifestyle $lifestyle = null;

    #[JMS\Groups(['action'])]
    #[JMS\Type('OPG\Digideps\Backend\Entity\Report\Action')]
    #[ORM\OneToOne(mappedBy: 'report', targetEntity: Action::class, cascade: ['persist', 'remove'])]
    private ?Action $action = null;

    #[JMS\Groups(['mental-capacity'])]
    #[JMS\Type('OPG\Digideps\Backend\Entity\Report\MentalCapacity')]
    #[ORM\OneToOne(mappedBy: 'report', targetEntity: MentalCapacity::class, cascade: ['persist', 'remove'])]
    private ?MentalCapacity $mentalCapacity = null;

    #[JMS\Groups(['client-benefits-check'])]
    #[JMS\Type('OPG\Digideps\Backend\Entity\Report\ClientBenefitsCheck')]
    #[ORM\OneToOne(mappedBy: 'report', targetEntity: ClientBenefitsCheck::class, cascade: ['persist', 'remove'])]
    private ?ClientBenefitsCheck $clientBenefitsCheck = null;

    #[JMS\Groups(['report', 'report-period'])]
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[ORM\Column(name: 'start_date', type: 'date', nullable: true)]
    private \DateTime $startDate;

    #[JMS\Groups(['report', 'report-period'])]
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[ORM\Column(name: 'due_date', type: 'date', nullable: true)]
    private \DateTime $dueDate;

    #[JMS\Groups(['report', 'report-period'])]
    #[JMS\Accessor(getter: 'getEndDate')]
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[ORM\Column(name: 'end_date', type: 'date', nullable: true)]
    private \DateTime $endDate;

    #[JMS\Groups(['report'])]
    #[JMS\Accessor(getter: 'getSubmitDate')]
    #[JMS\Type('DateTime')]
    #[ORM\Column(name: 'submit_date', type: 'datetime', nullable: true)]
    private ?\DateTime $submitDate = null;

    #[JMS\Accessor(getter: 'getUnSubmitDate')]
    #[JMS\Groups(['report'])]
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[ORM\Column(name: 'un_submit_date', type: 'datetime', nullable: true)]
    private ?\DateTime $unSubmitDate = null;

    #[JMS\Groups(['report'])]
    #[JMS\Type('boolean')]
    #[ORM\Column(name: 'submitted', type: 'boolean', nullable: true)]
    private ?bool $submitted = null;

    #[JMS\Groups(['report-submitted-by'])]
    #[JMS\Type('OPG\Digideps\Backend\Entity\User')]
    #[ORM\JoinColumn(name: 'submitted_by', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $submittedBy = null;

    /**
     * @deprecated client shouldn't need this anymore
     */
    #[JMS\Groups(['report'])]
    #[JMS\Type('boolean')]
    #[ORM\Column(name: 'report_seen', type: 'boolean', options: ['default' => true])]
    private bool $reportSeen = true;

    /**
     * not_deputy|only_deputy|more_deputies_behalf|more_deputies_not_behalf
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report'])]
    #[ORM\Column(name: 'agreed_behalf_deputy', type: 'string', length: 50, nullable: true)]
    private ?string $agreedBehalfDeputy = null;

    /**
     * required if agreedBehalfDeputy == more_deputies_not_behalf
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report'])]
    #[ORM\Column(name: 'agreed_behalf_deputy_explanation', type: 'text', nullable: true)]
    private ?string $agreedBehalfDeputyExplanation = null;

    /**
     * @var Collection<int, Document>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\Document>')]
    #[JMS\Groups(['report-documents'])]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: Document::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    #[ORM\OrderBy(['createdOn' => 'DESC', 'fileName' => 'ASC'])]
    private Collection $documents;

    /**
     * @var Collection<int, ReportSubmission> $reportSubmissions
     */
    #[JMS\Type("ArrayCollection<OPG\Digideps\Backend\Entity\Report\ReportSubmission>")]
    #[JMS\Groups(['document-sync'])]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: ReportSubmission::class, fetch: 'EXTRA_LAZY')]
    private Collection $reportSubmissions;

    #[JMS\Groups(['report', 'wish-to-provide-documentation'])]
    #[JMS\Type('string')]
    #[ORM\Column(name: 'wish_to_provide_documentation', type: 'string', nullable: true)]
    private ?string $wishToProvideDocumentation;

    /**
     * yes/no/null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'current-prof-payments-received'])]
    #[ORM\Column(name: 'current_prof_payments_received', type: 'string', nullable: true)]
    private ?string $currentProfPaymentsReceived = null;

    /**
     * yes/no/null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'report-prof-estimate-fees'])]
    #[ORM\Column(name: 'previous_prof_fees_estimate_given', type: 'string', length: 3, nullable: true)]
    private ?string $previousProfFeesEstimateGiven = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'report-prof-estimate-fees'])]
    #[ORM\Column(name: 'prof_fees_estimate_scco_reason', type: 'text', nullable: true)]
    private ?string $profFeesEstimateSccoReason = null;

    /**
     * yes/no/null (see constants)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'significantDecisionsMade'])]
    #[ORM\Column(name: 'significant_decisions_made', type: 'text', nullable: true)]
    private ?string $significantDecisionsMade = null;

    #[JMS\Groups(['report'])]
    #[JMS\Type('string')]
    #[ORM\Column(name: 'unsubmitted_sections_list', type: 'text', nullable: true)]
    private ?string $unsubmittedSectionsList = null;

    #[JMS\Groups(['report', 'report-checklist'])]
    #[JMS\Type('OPG\Digideps\Backend\Entity\Report\Checklist')]
    #[ORM\OneToOne(mappedBy: 'report', targetEntity: Checklist::class, cascade: ['persist', 'remove'])]
    private ?Checklist $checklist = null;

    #[JMS\Groups(['report', 'report-checklist'])]
    #[JMS\Type('OPG\Digideps\Backend\Entity\Report\ReviewChecklist')]
    #[ORM\OneToOne(mappedBy: 'report', targetEntity: ReviewChecklist::class, cascade: ['persist', 'remove'])]
    private ?ReviewChecklist $reviewChecklist = null;

    #[JMS\Type('OPG\Digideps\Backend\Entity\Satisfaction')]
    #[JMS\Groups(['user-research', 'satisfaction'])]
    #[ORM\OneToOne(mappedBy: 'report', targetEntity: Satisfaction::class, cascade: ['persist', 'remove'])]
    private ?Satisfaction $satisfaction = null;

    /**
     * null| yes | no
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'doesMoneyInExist'])]
    #[ORM\Column(name: 'money_in_exists', type: 'text', nullable: true)]
    private ?string $moneyInExists = null;

    /**
     * Captures reason for no money in. Required if no money has gone in
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'reasonForNoMoneyIn'])]
    #[ORM\Column(name: 'reason_for_no_money_in', type: 'text', nullable: true)]
    private ?string $reasonForNoMoneyIn = null;

    /**
     * yes | no | null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'doesMoneyOutExist'])]
    #[ORM\Column(name: 'money_out_exists', type: 'text', nullable: true)]
    private ?string $moneyOutExists = null;

    /**
     * Captures reason for no money out. Required if no money has gone out
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'reasonForNoMoneyOut'])]
    #[ORM\Column(name: 'reason_for_no_money_out', type: 'text', nullable: true)]
    private ?string $reasonForNoMoneyOut = null;

    /**
     * @var Collection<int, CourtOrder> $courtOrders
     */
    #[JMS\Groups(['report-with-court-orders'])]
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\CourtOrder>')]
    #[ORM\ManyToMany(targetEntity: CourtOrder::class, mappedBy: 'reports', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private Collection $courtOrders;

    private ReportType $reportType;
    private Sections $sections;
    private ?\DateTime $benefitsSectionReleaseDate = null;

    /**
     * Report constructor.
     *
     * @param bool $dateChecks if true, perform checks around multiple reports and dates. Useful for PA upload
     */
    public function __construct(Client $client, string $type, \DateTime $startDate, \DateTime $endDate, bool $dateChecks = true)
    {
        $reportType = ReportType::tryFrom($type);
        if ($reportType === null) {
            throw new \InvalidArgumentException("$type not a valid report type");
        }
        $this->reportType = $reportType;
        $this->sections = Sections::new($this->reportType);
        $this->type = $type;
        $this->client = $client;
        $this->startDate = new \DateTime($startDate->format('Y-m-d'), new \DateTimeZone('Europe/London'));
        $this->endDate = new \DateTime($endDate->format('Y-m-d'), new \DateTimeZone('Europe/London'));
        $this->updateDueDateBasedOnEndDate();

        if ($dateChecks && count($client->getUnsubmittedReports()) > 0) {
            throw new \RuntimeException('Client ' . $client->getId() . ' already has an unsubmitted report. Cannot create another one');
        }

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
            if ($daysDiff !== 0) {
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
        $this->checklist = null;
        $this->profDeputyPreviousCosts = new ArrayCollection();
        $this->profDeputyInterimCosts = new ArrayCollection();
        $this->profDeputyEstimateCosts = new ArrayCollection();
        $this->courtOrders = new ArrayCollection();
        $this->profDeputyOtherCosts = new ArrayCollection();

        // set sections as notStarted when a new report is created
        $statusCached = [];
        foreach ($this->getAvailableSections() as $sectionId) {
            $statusCached[$sectionId] = ['state' => ReportStatusService::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }

        $this->setSectionStatusesCached($statusCached);
        $this->reportStatusCached = self::STATUS_NOT_STARTED;
    }


    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function setId(int $id): static
    {
        if ($this->id === null) {
            $this->id = $id;
            $this->addMissingChildEntities();
        } elseif ($id === 0) {
            throw new \DomainException('You may not set the id of an entity to zero.');
        } else {
            throw new \LogicException('You may not set the id of an entity more than once.');
        }

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * See TYPE_ constants
     */
    public function setType(string $type): static
    {
        $this->reportType = ReportType::from($type);
        $this->sections = Sections::new($this->reportType);
        $this->type = $type;

        return $this;
    }

    public function updateDueDateBasedOnEndDate(): void
    {
        $this->dueDate = clone $this->endDate;
        if ($this->isLayReport()) {
            $this->dueDate->add(new \DateInterval('P21D'));
        } else {
            $this->dueDate->add(new \DateInterval('P56D'));
        }
    }

    /**
     * Get sections based on the report type
     *
     * @return array<string>
     */
    #[JMS\VirtualProperty]
    #[JMS\Groups(['report', 'report-sections'])]
    #[JMS\Type('array')]
    public function getAvailableSections(): array
    {
        return array_map(fn (ReportSection $section) => $section->value, [...$this->sections->getIterator()]);
    }

    /**
     * See SECTION_ constants
     */
    public function hasSection(string $section): bool
    {
        $enum = ReportSection::tryFrom($section);
        return $enum !== null && $this->sections->hasSection($enum);
    }

    public function setStartDate(\DateTime $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    public function setEndDate(\DateTime $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

    public function setSubmitDate(?\DateTime $submitDate): static
    {
        $this->submitDate = $submitDate;

        return $this;
    }

    public function getSubmitDate(): ?\DateTime
    {
        return $this->submitDate;
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

    public function setSubmitted(?bool $submitted): static
    {
        $this->submitted = $submitted;

        return $this;
    }

    public function getSubmitted(): ?bool
    {
        return $this->submitted;
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

    public function setClient(Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getClientId(): int
    {
        return $this->client->getId();
    }

    public function getVisitsCare(): ?VisitsCare
    {
        return $this->visitsCare;
    }

    public function setVisitsCare(?VisitsCare $visitsCare): static
    {
        $this->visitsCare = $visitsCare;

        return $this;
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

    public function setAction(?Action $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getMentalCapacity(): ?MentalCapacity
    {
        return $this->mentalCapacity;
    }

    public function setMentalCapacity(MentalCapacity $mentalCapacity): static
    {
        $this->mentalCapacity = $mentalCapacity;

        return $this;
    }

    public function setReportSeen(bool $reportSeen): static
    {
        $this->reportSeen = $reportSeen;

        return $this;
    }

    public function getReportSeen(): bool
    {
        return $this->reportSeen;
    }

    public function belongsToUser(User $user): bool
    {
        return in_array($user->getId(), $this->getClient()->getUserIds());
    }

    public function getAgreedBehalfDeputy(): ?string
    {
        return $this->agreedBehalfDeputy;
    }

    public function setAgreedBehalfDeputy(string $agreeBehalfDeputy): static
    {
        $acceptedValues = ['not_deputy', 'only_deputy', 'more_deputies_behalf', 'more_deputies_not_behalf'];

        if ($agreeBehalfDeputy && !in_array($agreeBehalfDeputy, $acceptedValues)) {
            throw new \InvalidArgumentException(__METHOD__ . " {$agreeBehalfDeputy} given. Expected value: " . implode(' or ', $acceptedValues));
        }

        $this->agreedBehalfDeputy = $agreeBehalfDeputy;

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

    #[JMS\VirtualProperty]
    #[JMS\SerializedName('is_due')]
    #[JMS\Groups(['report'])]
    public function isDue(): bool
    {
        return ReportService::isDue($this->getEndDate());
    }

    public function setDueDate(\DateTime $dueDate): static
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getDueDate(): \DateTime
    {
        return $this->dueDate;
    }

    public function isMissingMoneyOrAccountsOrClosingBalance(): bool
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
     */
    #[JMS\VirtualProperty]
    #[JMS\Type('boolean')]
    #[JMS\SerializedName('has106flag')]
    #[JMS\Groups(['report', 'report-106-flag'])]
    public function has106Flag(): bool
    {
        return $this->isPAreport();
    }

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    /**
     * Unsubmitted Reports.
     *
     * @return Collection<int, Document>
     */
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('unsubmitted_documents')]
    #[JMS\Groups(['documents'])]
    public function getUnsubmittedDocuments(): Collection
    {
        return $this->getDeputyDocuments()->filter(function ($d): bool {
            return empty($d->getReportSubmission());
        });
    }

    /**
     * Submitted reports.
     *
     * @return Collection<int, Document>
     */
    #[JMS\VirtualProperty('submittedDocuments')]
    #[JMS\SerializedName('submitted_documents')]
    #[JMS\Groups(['documents'])]
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\Document>')]
    public function getSubmittedDocuments(): Collection
    {
        return $this->getDeputyDocuments()->filter(function ($d): bool {
            return !empty($d->getReportSubmission());
        });
    }

    public function addDocument(Document $document): void
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
        }
    }

    /**
     * @return Collection<int, ReportSubmission>
     */
    #[JMS\SerializedName('report_submissions')]
    public function getReportSubmissions(): Collection
    {
        return $this->reportSubmissions;
    }

    public function addReportSubmission(ReportSubmission $submission): static
    {
        if (!$this->reportSubmissions->contains($submission)) {
            $this->reportSubmissions->add($submission);
        }

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

    public function getCurrentProfPaymentsReceived(): ?string
    {
        return $this->currentProfPaymentsReceived;
    }

    public function setCurrentProfPaymentsReceived(?string $currentProfPaymentsReceived): void
    {
        $this->currentProfPaymentsReceived = $currentProfPaymentsReceived;
    }

    public function getPreviousProfFeesEstimateGiven(): ?string
    {
        return $this->previousProfFeesEstimateGiven;
    }

    public function setPreviousProfFeesEstimateGiven(?string $previousProfFeesEstimateGiven): static
    {
        $this->previousProfFeesEstimateGiven = $previousProfFeesEstimateGiven;

        return $this;
    }

    public function getProfFeesEstimateSccoReason(): ?string
    {
        return $this->profFeesEstimateSccoReason;
    }

    public function setProfFeesEstimateSccoReason(?string $profFeesEstimateSccoReason): static
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
     * @return Collection<int, Document>
     */
    public function getDeputyDocuments(): Collection
    {
        return $this->getDocuments()->filter(function ($document): bool {
            /* @var $document Document */
            return !($document->isAdminDocument() || $document->isReportPdf());
        });
    }

    public function getChecklist(): ?Checklist
    {
        return $this->checklist;
    }

    public function setChecklist(Checklist $checklist): void
    {
        $this->checklist = $checklist;
    }

    public function getReviewChecklist(): ?ReviewChecklist
    {
        return $this->reviewChecklist;
    }

    public function setReviewChecklist(ReviewChecklist $reviewChecklist): void
    {
        $this->reviewChecklist = $reviewChecklist;
    }

    /**
     * Previous report data. Just return id and type for second api call to allow new JMS groups.
     */
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('previous_report_data')]
    #[JMS\Groups(['previous-report-data'])]
    #[JMS\Type('array')]
    public function getPreviousReportData(): array
    {
        $values = $this->getClient()->getReports()->getValues();

        // sort so highest ID is first in the list
        uasort(
            $values,
            function ($a, $b): int {
                return ($a->getId() > $b->getId()) ? -1 : 1;
            }
        );

        $orderedClientReports = new ArrayCollection($values);

        foreach ($orderedClientReports as $clientReport) {
            if ($clientReport->getId() < $this->getId()) {
                // if ID is lower, it implies that the report with a lower ID is before the current report
                return [
                    'report-summary' => $clientReport->getReportSummary(),
                    'financial-summary' => $clientReport->getFinancialSummary(),
                ];
            }
        }

        return [];
    }

    /**
     * Report financial summary, contains bank accounts and balance information.
     */
    public function getFinancialSummary(): array
    {
        $accounts = [];
        $openingBalanceTotal = 0;
        foreach ($this->getBankAccounts() as $ba) {
            $accounts[$ba->getId()]['nameOneLine'] = $ba->getNameOneLine();
            $accounts[$ba->getId()]['bank'] = $ba->getBank();
            $accounts[$ba->getId()]['accountType'] = $ba->getAccountTypeText();
            $accounts[$ba->getId()]['openingBalance'] = $ba->getOpeningBalance();
            $accounts[$ba->getId()]['closingBalance'] = $ba->getClosingBalance();
            $accounts[$ba->getId()]['isClosed'] = $ba->getIsClosed();
            $accounts[$ba->getId()]['isJointAccount'] = $ba->getIsJointAccount();

            $openingBalanceTotal += (float)$ba->getOpeningBalance();
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
     */
    public function getReportSummary(): array
    {
        return [
            'type' => $this->getType(),
        ];
    }

    /**
     * Returns the translation key relating to the type of report. Hybrids identified to determine any suffix required
     * for the translation keys (translations are in 'report' domain).
     */
    #[JMS\VirtualProperty]
    #[JMS\Groups(['report'])]
    #[JMS\Type('string')]
    public function getReportTitle(): string
    {
        $hybrid = $this->reportType->courtOrderKind === CourtOrderKind::Hybrid ? 'HealthWelfare' : '';

        return match ($this->reportType->courtOrderReportType) {
            CourtOrderReportType::OPG102 => "propertyAffairsGeneral$hybrid",
            CourtOrderReportType::OPG103 => "propertyAffairsMinimal$hybrid",
            CourtOrderReportType::OPG104 => 'healthWelfare',
        };
    }

    public function isLayReport(): bool
    {
        return $this->reportType->deputyType === DeputyType::LAY;
    }

    public function isPAreport(): bool
    {
        return $this->reportType->deputyType === DeputyType::PA;
    }

    public function isProfReport(): bool
    {
        return $this->reportType->deputyType === DeputyType::PRO;
    }

    public function getSatisfaction(): ?Satisfaction
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
     * The client benefits check section of the report should be required for:
     *
     * Reports with an unsubmit date that had originally completed the section.
     * Reports without an unsubmit date and a due date more than 60 days after the client benefits section release date.
     */
    public function requiresBenefitsCheckSection(): bool
    {
        if ($this->getUnSubmitDate()) {
            return $this->getClientBenefitsCheck() instanceof ClientBenefitsCheck;
        } else {
            // Provides a positive or negative string showing days between feature flag and due date
            $diffInDays = (int)$this->getBenefitsSectionReleaseDate()?->diff($this->getDueDate())?->format('%R%a');

            return $diffInDays > self::BENEFITS_CHECK_SECTION_REQUIRED_GRACE_PERIOD_DAYS;
        }
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

    /**
     * @return array<string>
     */
    public static function allRolesAllReportTypes(): array
    {
        return [
            self::LAY_PFA_LOW_ASSETS_TYPE, self::LAY_PFA_HIGH_ASSETS_TYPE, self::LAY_HW_TYPE, self::LAY_COMBINED_LOW_ASSETS_TYPE, self::LAY_COMBINED_HIGH_ASSETS_TYPE,
            self::PA_PFA_LOW_ASSETS_TYPE, self::PA_PFA_HIGH_ASSETS_TYPE, self::PA_HW_TYPE, self::PA_COMBINED_LOW_ASSETS_TYPE, self::PA_COMBINED_HIGH_ASSETS_TYPE,
            self::PROF_PFA_LOW_ASSETS_TYPE, self::PROF_PFA_HIGH_ASSETS_TYPE, self::PROF_HW_TYPE, self::PROF_COMBINED_LOW_ASSETS_TYPE, self::PROF_COMBINED_HIGH_ASSETS_TYPE,
        ];
    }

    /**
     * @return array<string>
     */
    public static function allRolesHwAndCombinedReportTypes(): array
    {
        return [
            self::LAY_HW_TYPE, self::LAY_COMBINED_LOW_ASSETS_TYPE, self::LAY_COMBINED_HIGH_ASSETS_TYPE,
            self::PA_HW_TYPE, self::PA_COMBINED_LOW_ASSETS_TYPE, self::PA_COMBINED_HIGH_ASSETS_TYPE,
            self::PROF_HW_TYPE, self::PROF_COMBINED_LOW_ASSETS_TYPE, self::PROF_COMBINED_HIGH_ASSETS_TYPE,
        ];
    }

    /**
     * @return array<string>
     */
    public static function allRolesPfaAndCombinedReportTypes(): array
    {
        return [
            self::LAY_PFA_LOW_ASSETS_TYPE, self::LAY_PFA_HIGH_ASSETS_TYPE, self::LAY_COMBINED_LOW_ASSETS_TYPE, self::LAY_COMBINED_HIGH_ASSETS_TYPE,
            self::PA_PFA_LOW_ASSETS_TYPE, self::PA_PFA_HIGH_ASSETS_TYPE, self::PA_COMBINED_LOW_ASSETS_TYPE, self::PA_COMBINED_HIGH_ASSETS_TYPE,
            self::PROF_PFA_LOW_ASSETS_TYPE, self::PROF_PFA_HIGH_ASSETS_TYPE, self::PROF_COMBINED_LOW_ASSETS_TYPE, self::PROF_COMBINED_HIGH_ASSETS_TYPE,
        ];
    }

    /**
     * @return array<string>
     */
    public static function allProfReportTypes(): array
    {
        return [
            self::PROF_PFA_LOW_ASSETS_TYPE, self::PROF_PFA_HIGH_ASSETS_TYPE, self::PROF_HW_TYPE, self::PROF_COMBINED_LOW_ASSETS_TYPE, self::PROF_COMBINED_HIGH_ASSETS_TYPE,
        ];
    }

    /**
     * @return array<string>
     */
    public static function allRolesPfaAndCombinedHighAssetsReportTypes(): array
    {
        return [
            self::LAY_PFA_HIGH_ASSETS_TYPE, self::LAY_COMBINED_HIGH_ASSETS_TYPE,
            self::PA_PFA_HIGH_ASSETS_TYPE, self::PA_COMBINED_HIGH_ASSETS_TYPE,
            self::PROF_PFA_HIGH_ASSETS_TYPE, self::PROF_COMBINED_HIGH_ASSETS_TYPE,
        ];
    }

    /**
     * @return array<string>
     */
    public static function allRolesPfaAndCombinedLowAssetsReportTypes(): array
    {
        return [
            self::LAY_PFA_LOW_ASSETS_TYPE, self::LAY_COMBINED_LOW_ASSETS_TYPE,
            self::PA_PFA_LOW_ASSETS_TYPE, self::PA_COMBINED_LOW_ASSETS_TYPE,
            self::PROF_PFA_LOW_ASSETS_TYPE, self::PROF_COMBINED_LOW_ASSETS_TYPE,
        ];
    }

    /**
     * @return array<string>
     */
    public static function layPfaAndCombinedReportTypes(): array
    {
        return [
            self::LAY_PFA_LOW_ASSETS_TYPE, self::LAY_PFA_HIGH_ASSETS_TYPE, self::LAY_COMBINED_LOW_ASSETS_TYPE, self::LAY_COMBINED_HIGH_ASSETS_TYPE,
        ];
    }

    /**
     * @return array<string>
     */
    public static function paPfaAndCombinedReportTypes(): array
    {
        return [
            self::PA_PFA_LOW_ASSETS_TYPE, self::PA_PFA_HIGH_ASSETS_TYPE, self::PA_COMBINED_LOW_ASSETS_TYPE, self::PA_COMBINED_HIGH_ASSETS_TYPE,
        ];
    }
    /**
     * @return array<string>
     */
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

    public function isHybrid(): bool
    {
        return $this->reportType->courtOrderKind === CourtOrderKind::Hybrid;
    }

    public function isPfa(): bool
    {
        return $this->reportType->courtOrderType === CourtOrderType::PFA;
    }

    public function isHw(): bool
    {
        return $this->reportType->courtOrderType === CourtOrderType::HW;
    }

    /**
     * @return Collection<int, CourtOrder>
     */
    public function getCourtOrders(): Collection
    {
        return $this->courtOrders;
    }

    /**
     * @return array<CourtOrder>
     */
    public function getActiveCourtOrders(): array
    {
        $active = [];

        foreach ($this->courtOrders as $courtOrder) {
            if ($courtOrder->getStatus() === 'ACTIVE') {
                $active[] = $courtOrder;
            }
        }

        return $active;
    }

    #[ORM\PrePersist]
    public function onPrePersist(PrePersistEventArgs $_): void
    {
        if ($this->id === null) {
            $this->addMissingChildEntities();
        }
    }

    #[ORM\PostLoad]
    public function onPostLoad(PostLoadEventArgs $_): void
    {
        $this->reportType = ReportType::from($this->type);
        $this->sections = Sections::new($this->reportType);
    }

    private function addMissingChildEntities(): void
    {
        if ($this->getDebts()->isEmpty()) {
            foreach (Debt::$debtTypeIds as $row) {
                new Debt($this, $row[0], $row[1], null);
            }
        }
        if ($this->getFees()->isEmpty()) {
            foreach (Fee::$feeTypeIds as $id => $row) {
                new Fee($this, $id, null);
            }
        }
        if ($this->getMoneyShortCategories()->isEmpty()) {
            $this->moneyShortCategories = new ArrayCollection();

            foreach (MoneyShortCategory::getCategories(null) as $typeId => $_) {
                $this->moneyShortCategories->add(new MoneyShortCategory($this, $typeId, false));
            }
        }
    }

    public function getReportType(): ReportType
    {
        return $this->reportType;
    }
}
