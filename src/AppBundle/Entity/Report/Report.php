<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Traits as ReportTraits;
use AppBundle\Entity\User;
use AppBundle\Service\ReportStatusService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Reports.
 *
 * @ORM\Table(name="report")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\ReportRepository")
 */
class Report
{
    use ReportTraits\AssetTrait;
    use ReportTraits\BankAccountTrait;
    use ReportTraits\BalanceTrait;
    use ReportTraits\ContactTrait;
    use ReportTraits\DecisionTrait;
    use ReportTraits\ExpensesTrait;
    use ReportTraits\GiftsTrait;
    use ReportTraits\MoneyShortTrait;
    use ReportTraits\MoneyTransactionTrait;
    use ReportTraits\MoneyTransferTrait;
    use ReportTraits\MoreInfoTrait;
    use ReportTraits\DebtTrait;
    use ReportTraits\PaFeeExpensesTrait;

    const HEALTH_WELFARE = 1;
    const PROPERTY_AND_AFFAIRS = 2;

    const TYPE_102 = '102';
    const TYPE_103 = '103';
    const TYPE_104 = '104';

    // feature flags, to disable 103/104 if/when needed
    const ENABLE_103 = true;
    const ENABLE_104 = false;

    /**
     * Reports with total amount of assets
     * Threshold under which reports should be 103, and not 102
     */
    const ASSETS_TOTAL_VALUE_103_THRESHOLD = 21000;

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
     * TODO: consider using Doctrine table inheritance on report.type
     *
     * @var string
     *             see TYPE_ class constants
     *
     * @JMS\Groups({"report"})
     * @JMS\Type("string")
     * @ORM\Column(name="type", type="string", length=3, nullable=false)
     */
    private $type;

    /**
     * @var int
     *
     * @JMS\Groups({"client"})
     * @JMS\Type("AppBundle\Entity\Client")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Client", inversedBy="reports")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    private $client;

    /**
     * @var VisitsCare
     *
     * @JMS\Groups({"visits-care"})
     * @JMS\Type("AppBundle\Entity\Report\VisitsCare")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Report\VisitsCare",  mappedBy="report", cascade={"persist"})
     **/
    private $visitsCare;

    /**
     * @var Action
     *
     * @JMS\Groups({"action"})
     * @JMS\Type("AppBundle\Entity\Report\Action")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Report\Action",  mappedBy="report", cascade={"persist"})
     **/
    private $action;

    /**
     * @var MentalCapacity
     *
     * @JMS\Groups({ "mental-capacity"})
     * @JMS\Type("AppBundle\Entity\Report\MentalCapacity")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Report\MentalCapacity",  mappedBy="report", cascade={"persist"})
     **/
    private $mentalCapacity;

    /**
     * @var \Date
     *
     * @JMS\Groups({"report"})
     * @JMS\Accessor(getter="getStartDate")
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @ORM\Column(name="start_date", type="date", nullable=true)
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @JMS\Groups({"report"})
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", fetch="EAGER")
     * @ORM\JoinColumn(name="submitted_by", referencedColumnName="id")
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
     * @deprecated data needed for previous data migration
     *
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report"})
     * @ORM\Column(name="metadata", type="text", nullable=true)
     */
    private $metadata;

    /**
     * @var ArrayCollection
     *
     * @JMS\Type("array<AppBundle\Entity\Report\Document>")
     * @JMS\Groups({"report-documents"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\Document", mappedBy="report", cascade={"persist"})
     * @ORM\OrderBy({"createdOn"="DESC"})
     */
    private $documents;

    /**
     * Report constructor
     * Construct reports using the report service
     *
     * @param Client $client
     */
    public function __construct(Client $client, $type, \DateTime $startDate, \DateTime $endDate)
    {
        if (!in_array($type, [self::TYPE_102, self::TYPE_103, self::TYPE_104])) {
            throw new \InvalidArgumentException("$type not a valid report type");
        }
        $this->type = $type;
        $this->client = $client;
        $this->startDate = new \DateTime($startDate->format('Y-m-d'));
        $this->endDate = new \DateTime($endDate->format('Y-m-d'));

        // date interval check
//        if ((int)$startDate->diff($endDate)->format('%a') > 365) {
//            throw new \RuntimeException('Report cannot cover more than one year');
//        }


        // check this there only is one unsubmitted report
        if ($client->getUnsubmittedReports()->count() > 0) {
            throw new \RuntimeException('Client '.$client->getId().' already has unsubmitted report. Cannot create another one');
        }

        // check date interval overlapping other reports
        if ($client->getSubmittedReports()->count()) {
            $unsubmittedEndDates = array_map(function($report){ return $report->getEndDate(); }, $client->getSubmittedReports()->toArray());
            rsort($unsubmittedEndDates); //order by last first
            $endDateLastReport = $unsubmittedEndDates[0];
            $expectedStartDate = clone $endDateLastReport;
            $expectedStartDate->modify('+1 day');
            $daysDiff = (int)$expectedStartDate->diff($this->startDate)->format('%a');
            if ($daysDiff !== 0) {
                throw new \RuntimeException(sprintf(
                    'Incorrect start date. Last submitted report was on %s, '
                    .'therefore the new report is expected to start on %s, not on %s',
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
     *  @return Report
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

    public function isDue()
    {
        if (!$this->getEndDate() instanceof \DateTime) {
            return false;
        }

        // reset time on dates
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        $reportDueOn = clone $this->getEndDate();
        $reportDueOn->setTime(0, 0, 0);

        return $today >= $reportDueOn;
    }

    /**
     * @return string
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param string $metadata
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * Function to get the due date for a report based on the logic that the due date is 8 weeks
     * after the end of the report period.
     *
     * @return bool|\DateTime
     */
    public function getDueDate()
    {
        if (!$this->getEndDate() instanceof \DateTime) {
            return false;
        }

        return $this->getEndDate()->add(new \DateInterval('P56D'));
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
     *
     * @JMS\VirtualProperty
     * @JMS\Type("boolean")
     * @JMS\SerializedName("has106flag")
     * @JMS\Groups({"report", "report-106-flag"})
     *
     */
    public function has106Flag()
    {
        return $this->getClient()->getUsers()->first()->isPaDeputy();
    }

    /**
     * Get sections status, using ReportStatusService built on this class.
     *
     * @JMS\VirtualProperty
     * @JMS\Groups({
     *     "status",
     *     "decision-status",
     *     "contact-status",
     *     "visits-care-state",
     *     "expenses-state",
     *     "gifts-state",
     *     "account-state",
     *     "money-transfer-state",
     *     "money-in-state",
     *     "money-out-state",
     *     "asset-state",
     *     "debt-state",
     *     "action-state",
     *     "more-info-state",
     *     "balance-state",
     *     "money-in-short-state",
     *     "money-out-short-state",
     *     "fee-state",
     * })
     *
     * @return ReportStatusService
     */
    public function getStatus()
    {
        return new ReportStatusService($this);
    }

    /**
     * @return ArrayCollection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param ArrayCollection $documents
     *
     * @return $this
     */
    public function setDocuments($documents)
    {
        $this->documents = $documents;
        return $this;
    }
}
