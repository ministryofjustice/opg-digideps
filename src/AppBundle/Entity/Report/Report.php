<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Client;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Traits as ReportTraits;
use AppBundle\Entity\ReportInterface;
use AppBundle\Entity\User;
use AppBundle\Service\ReportService;
use AppBundle\Service\ReportStatusService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Reports.
 *
 * @ORM\Table(name="report",
 *     indexes={
 *     @ORM\Index(name="end_date_idx", columns={"end_date"}),
 *     @ORM\Index(name="submitted_idx", columns={"submitted"}),
 *     @ORM\Index(name="report_status_cached_idx", columns={"report_status_cached"})
 *  })
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\ReportRepository")
 */
class Report implements ReportInterface
{
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
    use ReportTraits\StatusTrait;

    /**
     * Reports with total amount of assets
     * Threshold under which reports should be 103, and not 102
     */
    const ASSETS_TOTAL_VALUE_103_THRESHOLD = 21000;

    const HEALTH_WELFARE = 1;
    const PROPERTY_AND_AFFAIRS = 2;

    // https://opgtransform.atlassian.net/wiki/spaces/DEPDS/pages/135266255/Report+variations
    const TYPE_103 = '103';
    const TYPE_102 = '102';
    const TYPE_104 = '104';
    const TYPE_103_4 = '103-4';
    const TYPE_102_4 = '102-4';

    // PA
    const TYPE_103_6 = '103-6';
    const TYPE_102_6 = '102-6';
    const TYPE_104_6 = '104-6';
    const TYPE_103_4_6 = '103-4-6';
    const TYPE_102_4_6 = '102-4-6';

    // PROF
    const TYPE_103_5 = '103-5';
    const TYPE_102_5 = '102-5';
    const TYPE_104_5 = '104-5';
    const TYPE_103_4_5 = '103-4-5';
    const TYPE_102_4_5 = '102-4-5';

    const ENABLE_FEE_SECTIONS = false;

    private static $reportTypes = [
        self::TYPE_103, self::TYPE_102, self::TYPE_104, self::TYPE_103_4, self::TYPE_102_4,
        self::TYPE_103_6, self::TYPE_102_6, self::TYPE_104_6, self::TYPE_103_4_6, self::TYPE_102_4_6,
        self::TYPE_103_5, self::TYPE_102_5, self::TYPE_104_5, self::TYPE_103_4_5, self::TYPE_102_4_5
    ];

    const SECTION_DECISIONS = 'decisions';
    const SECTION_CONTACTS = 'contacts';
    const SECTION_VISITS_CARE = 'visitsCare';
    const SECTION_LIFESTYLE = 'lifestyle';

    // money
    const SECTION_BALANCE = 'balance'; // not a real section, but needed as a flag for the view and the validation
    const SECTION_BANK_ACCOUNTS = 'bankAccounts';
    const SECTION_MONEY_TRANSFERS = 'moneyTransfers';
    const SECTION_MONEY_IN = 'moneyIn';
    const SECTION_MONEY_OUT = 'moneyOut';
    const SECTION_MONEY_IN_SHORT = 'moneyInShort';
    const SECTION_MONEY_OUT_SHORT = 'moneyOutShort';
    const SECTION_ASSETS = 'assets';
    const SECTION_DEBTS = 'debts';
    const SECTION_GIFTS = 'gifts';
    // end money

    const SECTION_ACTIONS = 'actions';
    const SECTION_OTHER_INFO = 'otherInfo';
    const SECTION_DEPUTY_EXPENSES = 'deputyExpenses';
    const SECTION_PA_DEPUTY_EXPENSES = 'paDeputyExpenses'; //106, AKA Fee and expenses

    const SECTION_PROF_CURRENT_FEES = 'profCurrentFees';

    const SECTION_DOCUMENTS = 'documents';

    /**
     * https://opgtransform.atlassian.net/wiki/spaces/DEPDS/pages/135266255/Report+variations
     *
     * @return array
     */
    public static function getSectionsSettings()
    {
        $allReports = [
            self::TYPE_103, self::TYPE_102, self::TYPE_104, self::TYPE_103_4, self::TYPE_102_4, //Lay
            self::TYPE_103_6, self::TYPE_102_6, self::TYPE_104_6, self::TYPE_103_4_6, self::TYPE_102_4_6, // PA
            self::TYPE_103_5, self::TYPE_102_5, self::TYPE_104_5, self::TYPE_103_4_5, self::TYPE_102_4_5, // Prof
        ];
        $r102n103 = [
            self::TYPE_103, self::TYPE_102, self::TYPE_103_4, self::TYPE_102_4, //Lay
            self::TYPE_103_6, self::TYPE_102_6, self::TYPE_103_4_6, self::TYPE_102_4_6, // PA
            self::TYPE_103_5, self::TYPE_102_5, self::TYPE_103_4_5, self::TYPE_102_4_5 // Prof
        ];
        $r104 = [
            self::TYPE_104, self::TYPE_103_4, self::TYPE_102_4, // Lay
            self::TYPE_104_6, self::TYPE_103_4_6, self::TYPE_102_4_6, // PA
            self::TYPE_104_5, self::TYPE_103_4_5, self::TYPE_102_4_5 // PA
        ];

        return [
            self::SECTION_DECISIONS          => $allReports,
            self::SECTION_CONTACTS           => $allReports,
            self::SECTION_VISITS_CARE        => $allReports,
            self::SECTION_LIFESTYLE          => $r104,
            // money
            self::SECTION_BANK_ACCOUNTS      => $r102n103,
            self::SECTION_MONEY_TRANSFERS    => [self::TYPE_102, self::TYPE_102_4, self::TYPE_102_6, self::TYPE_102_4_6, self::TYPE_102_5, self::TYPE_102_4_5],
            self::SECTION_MONEY_IN           => [self::TYPE_102, self::TYPE_102_4, self::TYPE_102_6, self::TYPE_102_4_6, self::TYPE_102_5, self::TYPE_102_4_5],
            self::SECTION_MONEY_OUT          => [self::TYPE_102, self::TYPE_102_4, self::TYPE_102_6, self::TYPE_102_4_6, self::TYPE_102_5, self::TYPE_102_4_5],
            self::SECTION_MONEY_IN_SHORT     => [self::TYPE_103, self::TYPE_103_4, self::TYPE_103_6, self::TYPE_103_4_6, self::TYPE_103_5, self::TYPE_103_4_5],
            self::SECTION_MONEY_OUT_SHORT    => [self::TYPE_103, self::TYPE_103_4, self::TYPE_103_6, self::TYPE_103_4_6, self::TYPE_103_5, self::TYPE_103_4_5],
            self::SECTION_ASSETS             => $r102n103,
            self::SECTION_DEBTS              => $r102n103,
            self::SECTION_GIFTS              => $r102n103,
            self::SECTION_BALANCE            => [self::TYPE_102, self::TYPE_102_4, self::TYPE_102_6, self::TYPE_102_4_6, self::TYPE_102_5, self::TYPE_102_4_5],
            // end money
            self::SECTION_ACTIONS            => $allReports,
            self::SECTION_OTHER_INFO         => $allReports,
            self::SECTION_DEPUTY_EXPENSES    => [self::TYPE_103, self::TYPE_102, self::TYPE_103_4, self::TYPE_102_4], // Lay except 104
            self::SECTION_PA_DEPUTY_EXPENSES => [
                self::TYPE_103_6, self::TYPE_102_6, self::TYPE_103_4_6, self::TYPE_102_4_6, // PA except 104-6
            ],
            self::SECTION_PROF_CURRENT_FEES => self::ENABLE_FEE_SECTIONS ? [
                self::TYPE_103_5, self::TYPE_102_5, self::TYPE_103_4_5, self::TYPE_102_4_5, // Prof except 104-6
            ] : [],
            self::SECTION_DOCUMENTS          => $allReports,
        ];
    }

    /**
     * @var int
     *
     * @JMS\Groups({"report", "report-id"})
     * @JMS\Type("integer")
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="report_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string TYPE_ constants
     *
     * @JMS\Groups({"report", "report-type"})
     * @JMS\Type("string")
     * @ORM\Column(name="type", type="string", length=10, nullable=false)
     */
    private $type;

    /**
     * @var int
     *
     * @JMS\Groups({"report-client"})
     * @JMS\Type("AppBundle\Entity\Client")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Client", inversedBy="reports")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $client;

    /**
     * @var VisitsCare
     *
     * @JMS\Groups({"visits-care"})
     * @JMS\Type("AppBundle\Entity\Report\VisitsCare")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Report\VisitsCare",  mappedBy="report", cascade={"persist", "remove"}, fetch="LAZY")
     **/
    private $visitsCare;

    /**
     * @var Lifestyle
     *
     * @JMS\Groups({"lifestyle"})
     * @JMS\Type("AppBundle\Entity\Report\Lifestyle")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Report\Lifestyle",  mappedBy="report", cascade={"persist", "remove"})
     **/
    private $lifestyle;

    /**
     * @var Action
     *
     * @JMS\Groups({"action"})
     * @JMS\Type("AppBundle\Entity\Report\Action")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Report\Action",  mappedBy="report", cascade={"persist", "remove"})
     **/
    private $action;

    /**
     * @var MentalCapacity
     *
     * @JMS\Groups({ "mental-capacity"})
     * @JMS\Type("AppBundle\Entity\Report\MentalCapacity")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Report\MentalCapacity",  mappedBy="report", cascade={"persist", "remove"})
     **/
    private $mentalCapacity;

    /**
     * @var \DateTime
     *
     * @JMS\Groups({"report", "report-period"})
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @ORM\Column(name="start_date", type="date", nullable=true)
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @JMS\Groups({"report", "report-period"})
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @ORM\Column(name="due_date", type="date", nullable=true)
     */
    private $dueDate;

    /**
     * @var \DateTime
     *
     * @JMS\Groups({"report", "report-period"})
     * @JMS\Accessor(getter="getEndDate")
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @ORM\Column(name="end_date", type="date", nullable=true)
     */
    private $endDate;

    /**
     * @var \DateTime
     *
     * @JMS\Groups({"report"})
     * @JMS\Accessor(getter="getSubmitDate")
     * @JMS\Type("DateTime")
     * @ORM\Column(name="submit_date", type="datetime", nullable=true)
     */
    private $submitDate;

    /**
     * @var \DateTime
     *
     * @JMS\Groups({"report"})
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @ORM\Column(name="un_submit_date", type="datetime", nullable=true)
     */
    private $unSubmitDate;

    /**
     * @var \DateTime
     * @JMS\Accessor(getter="getLastedit")
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @ORM\Column(name="last_edit", type="datetime", nullable=true)
     */
    private $lastedit;

    /**
     * @var bool whether the report is submitted or not
     *
     * @JMS\Groups({"report"})
     * @JMS\Type("boolean")
     * @ORM\Column(name="submitted", type="boolean", nullable=true)
     */
    private $submitted;

    /**
     * @var User
     *
     * @JMS\Groups({"report-submitted-by"})
     * @JMS\Type("AppBundle\Entity\User")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="submitted_by", referencedColumnName="id", onDelete="SET NULL")
     */
    private $submittedBy;

    /**
     * @deprecated client shouldn't need this anymore
     *
     * @var bool
     * @JMS\Groups({"report"})
     * @JMS\Type("boolean")
     * @ORM\Column(name="report_seen", type="boolean", options={"default": true})
     */
    private $reportSeen;

    /**
     * @var string only_deputy|more_deputies_behalf|more_deputies_not_behalf
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report"})
     * @ORM\Column(name="agreed_behalf_deputy", type="string", length=50, nullable=true)
     */
    private $agreedBehalfDeputy;

    /**
     * @var string required if agreedBehalfDeputy == more_deputies_not_behalf
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report"})
     * @ORM\Column(name="agreed_behalf_deputy_explanation", type="text", nullable=true)
     */
    private $agreedBehalfDeputyExplanation;

    /**
     * @var ArrayCollection
     *
     * @JMS\Type("array<AppBundle\Entity\Report\Document>")
     * @JMS\Groups({"report-documents"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\Document", mappedBy="report", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"createdOn"="DESC"})
     */
    private $documents;

    /**
     * @JMS\Type("array<AppBundle\Entity\Report\ReportSubmission>")
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\ReportSubmission", mappedBy="report", fetch="EXTRA_LAZY")
     */
    private $reportSubmissions;

    /**
     * @var string
     * @JMS\Groups({"report", "wish-to-provide-documentation"})
     * @JMS\Type("string")
     * @ORM\Column(name="wish_to_provide_documentation", type="string", nullable=true)
     */
    private $wishToProvideDocumentation;

    /**
     * @var string yes/no
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report", "current-prof-payments-received"})
     * @ORM\Column(name="current_prof_payments_received", type="string", nullable=true)
     */
    private $currentProfPaymentsReceived;

    /**
     * @var string yes/no
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report", "report-prof-estimate-fees"})
     * @ORM\Column(name="previous_prof_fees_estimate_given", length=3, type="string", nullable=true)
     */
    private $previousProfFeesEstimateGiven;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report", "report-prof-estimate-fees"})
     * @ORM\Column(name="prof_fees_estimate_scco_reason", type="text", nullable=true)
     */
    private $profFeesEstimateSccoReason;

    /**
     * @var array
     *
     * @JMS\Groups({"report"})
     * @ORM\Column(name="unsubmitted_sections_list", type="text", nullable=true)
     *
     * @JMS\Type("string")
     */
    private $unsubmittedSectionsList;


    /**
     * @var Checklist
     *
     * // TODO "report" group is used by deputy side each time a report is loaded. Better to use a new one like report-checklist
     *
     * @JMS\Groups({"report"})
     * @JMS\Type("AppBundle\Entity\Report\Checklist")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Report\Checklist", mappedBy="report", cascade={"persist", "remove"})
     */
    private $checklist;

    const STATUS_NOT_STARTED = 'notStarted';
    const STATUS_READY_TO_SUBMIT = 'readyToSubmit';
    const STATUS_NOT_FINISHED = 'notFinished';

    /**
     * Report constructor.
     *
     * @param Client $client
     * @param $type
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param bool      $dateChecks if true, perform checks around multiple reports and dates. Useful for PA upload
     */
    public function __construct(Client $client, $type, \DateTime $startDate, \DateTime $endDate, $dateChecks = true)
    {
        if (!in_array($type, self::$reportTypes)) {
            throw new \InvalidArgumentException("$type not a valid report type");
        }
        $this->type = $type;
        $this->client = $client;
        $this->startDate = new \DateTime($startDate->format('Y-m-d'));
        $this->endDate = new \DateTime($endDate->format('Y-m-d'));
        $this->updateDueDateBasedOnEndDate();

        if ($dateChecks && count($client->getUnsubmittedReports()) > 0) {
            throw new \RuntimeException('Client ' . $client->getId() . ' already has an unsubmitted report. Cannot create another one');
        }

        // check date interval overlapping other reports
        if ($dateChecks && count($client->getSubmittedReports())) {
            $unsubmittedEndDates = array_map(function ($report) {
                return $report->getEndDate();
            }, $client->getSubmittedReports()->toArray());
            rsort($unsubmittedEndDates); //order by last first
            $endDateLastReport = $unsubmittedEndDates[0];
            $expectedStartDate = clone $endDateLastReport;
            $expectedStartDate->modify('+1 day');
            $daysDiff = (int) $expectedStartDate->diff($this->startDate)->format('%a');
            if ($daysDiff !== 0) {
                throw new \RuntimeException(sprintf(
                    'Incorrect start date. Last submitted report was on %s, '
                    . 'therefore the new report is expected to start on %s, not on %s',
                    $endDateLastReport->format('d/m/Y'),
                    $expectedStartDate->format('d/m/Y'),
                    $this->startDate->format('d/m/Y')
                ));
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

        // set sections as notStarted when a new report is created
        $statusCached = [];
        foreach($this->getAvailableSections() as $sectionId) {
            $statusCached[$sectionId] = ['state' => ReportStatusService::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }
        $this->setStatusCached($statusCached);
        $this->statusStatusCached = self::STATUS_NOT_STARTED;
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
     * set Due date to +8 weeks after end date
     */
    public function updateDueDateBasedOnEndDate()
    {
        // due date set to 8 exactly weeks (56 days) after the start date
        $this->dueDate = clone $this->endDate;
        $this->dueDate->add(new \DateInterval('P56D'));
    }

    /**
     * Get sections based on the report type
     *
     * @JMS\VirtualProperty
     * @JMS\Groups({"report"})
     * @JMS\Type("array")
     */
    public function getAvailableSections()
    {
        $ret = [];
        foreach (self::getSectionsSettings() as $sectionId => $reportTypes) {
            if (in_array($this->getType(), $reportTypes)) {
                $ret[] = $sectionId;
            }
        }

        return $ret;
    }

    /**
     * @todo consider removal
     *
     * @param string $section
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
     * @param \DateTime $startDate
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
     * @param \DateTime $endDate
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
     * For check reasons
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
     * @param string $submitDate
     *
     * @return Report
     */
    public function setSubmitDate(\DateTime $submitDate = null)
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
     * @return \DateTime
     */
    public function getUnSubmitDate()
    {
        return $this->unSubmitDate;
    }

    /**
     * @param \DateTime $unSubmitDate
     *
     * @return Report
     */
    public function setUnSubmitDate($unSubmitDate)
    {
        $this->unSubmitDate = $unSubmitDate;

        return $this;
    }

    /**
     * Set lastedit.
     *
     * @param \DateTime $lastedit
     *
     * @return Report
     */
    public function setLastedit(\DateTime $lastedit)
    {
        $this->lastedit = new \DateTime($lastedit->format('Y-m-d'));

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

    /**
     * @return mixed
     */
    public function getSubmittedBy()
    {
        return $this->submittedBy;
    }

    /**
     * @param mixed $submittedBy
     *
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
     * @param Client $client
     *
     * @return Report
     */
    public function setClient(Client $client = null)
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
     * @param VisitsCare $visitsCare
     *
     * @return Report
     */
    public function setVisitsCare(VisitsCare $visitsCare = null)
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
     * @param \AppBundle\Entity\Report\Action $action
     *
     * @return \AppBundle\Entity\Report\Report
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

    /**
     * @param MentalCapacity $mentalCapacity
     */
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
     * @param User $user
     *
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
        $acceptedValues = ['only_deputy', 'more_deputies_behalf', 'more_deputies_not_behalf'];
        if ($agreeBehalfDeputy && !in_array($agreeBehalfDeputy, $acceptedValues)) {
            throw new \InvalidArgumentException(__METHOD__ . " {$agreeBehalfDeputy} given. Expected value: " . implode(' or ', $acceptedValues));
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

    public static function isDue(\DateTime $endDate = null)
    {
        if (!$endDate) {
            return false;
        }

        $endOfToday = new \DateTime('today midnight');

        return $endDate < $endOfToday;

        // reset time on dates
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        $reportDueOn = clone $endDate;
        $reportDueOn->setTime(0, 0, 0);

        return $today >= $reportDueOn;
    }

    /**
     * @param \DateTime $dueDate
     *
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
        return !$this->hasMoneyIn()
        || !$this->hasMoneyOut()
        || !$this->hasAccounts()
        || count($this->getBankAccountsIncomplete()) > 0;
    }

    /**
     * Temporary until specs gets more clear around report types
     * This value could be set at creation time if needed in the future.
     * Until now, 106 is for all the PAs, so we get this value from the (only) user accessing the report.
     * Not sure if convenient to implement a 106 separate report, as 106 is also both an 102 AND an 103
     *
     * if it has the 106 flag, the deputy expense section is replaced with a more detailed "PA deputy expense" section
     * //TODO remove from mocks
     *
     * @JMS\VirtualProperty
     * @JMS\Type("boolean")
     * @JMS\SerializedName("has106flag")
     * @JMS\Groups({"report", "report-106-flag"})
     *
     */
    public function has106Flag()
    {
        return substr($this->type, -2) === '-6';
    }

    /**
     * @return ArrayCollection|Document[]
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Unsubmitted Reports
     *
     * @JMS\VirtualProperty
     * @JMS\SerializedName("unsubmitted_documents")
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
     * Submitted reports
     *
     * @JMS\VirtualProperty
     * @JMS\SerializedName("submitted_documents")
     * @JMS\Groups({"documents"})
     *
     * @return ArrayCollection|Document[]
     */
    public function getSubmittedDocuments()
    {
        return $this->getDeputyDocuments()->filter(function ($d) {
            return !empty($d->getReportSubmission());
        });
    }

    /**
     * @param Document $document
     */
    public function addDocument(Document $document)
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
        }
    }

    /**
     * @return mixed
     */
    public function getReportSubmissions()
    {
        return $this->reportSubmissions;
    }

    /**
     * @param ReportSubmission $submissions
     *
     * @return Report
     */
    public function addReportSubmission(ReportSubmission $submission)
    {
        if (!$this->reportSubmissions->contains($submission)) {
            $this->reportSubmissions->add($submission);
        }
    }

    /**
     * @return null|string
     */
    public function getWishToProvideDocumentation()
    {
        return $this->wishToProvideDocumentation;
    }

    /**
     * @param mixed $wishToProvideDocumentation
     *
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

    /**
     * @return array
     */
    public function getUnsubmittedSectionsList()
    {
        return $this->unsubmittedSectionsList;
    }

    /**
     * @param array $unsubmittedSectionsList
     */
    public function setUnsubmittedSectionsList($unsubmittedSectionsList)
    {
        $this->unsubmittedSectionsList = $unsubmittedSectionsList;
    }

    /**
     * Returns a list of deputy only documents. Those that should be visible to deputies only.
     * Excludes Report PDF and transactions PDF
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
         * Previous report data. Just return id and type for second api call to allo new JMS groups
         *
         * @JMS\VirtualProperty
         * @JMS\SerializedName("previous_report_data")
         * @JMS\Groups({"previous-report-data"})
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
                'financial-summary' => $previousReport->getFinancialSummary()
            ];
        }

    /**
     * Method to identify and return previous report.
     *
     * @return Ndr|Report|bool|mixed
     */
    private function getPreviousReport() {
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
     * Report financial summary, contains bank accounts and balance information
     *
     * @return array
     */
    public function getFinancialSummary() {
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
            'closing-balance-total' => $this->getAccountsClosingBalanceTotal()
        ];
    }

    /**
     * Report summary, contains basic information about a report. Called via report.previousReportData so as not to
     * return everything.
     *
     * @return array
     */
    public function getReportSummary() {
        return [
            'type' => $this->getType(),
        ];
    }

    /**
     * Returns the translation key relating to the type of report. Hybrids identified to determine any suffix required
     * for the translation keys (translations are in 'report' domain)
     *
     * @JMS\VirtualProperty
     * @JMS\Groups({"report"})
     * @JMS\Type("string")
     * @return string
     */
    public function getReportTitle()
    {
        $titleTranslationKeys = [
            self::TYPE_103 => 'propertyAffairsMinimal',
            self::TYPE_102 => 'propertyAffairsGeneral',
            self::TYPE_104 => 'healthWelfare',
            self::TYPE_103_4 => 'propertyAffairsMinimalHealthWelfare',
            self::TYPE_102_4 => 'propertyAffairsGeneralHealthWelfare',

            self::TYPE_103_6 => 'propertyAffairsMinimal',
            self::TYPE_102_6 => 'propertyAffairsGeneral',
            self::TYPE_104_6 => 'healthWelfare',
            self::TYPE_103_4_6 => 'propertyAffairsMinimalHealthWelfare',
            self::TYPE_102_4_6 => 'propertyAffairsGeneralHealthWelfare',

            self::TYPE_103_5 => 'propertyAffairsMinimal',
            self::TYPE_102_5 => 'propertyAffairsGeneral',
            self::TYPE_104_5 => 'healthWelfare',
            self::TYPE_103_4_5 => 'propertyAffairsMinimalHealthWelfare',
            self::TYPE_102_4_5 => 'propertyAffairsGeneralHealthWelfare',
        ];

        return $titleTranslationKeys[$this->getType()];
    }



}
