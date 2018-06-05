<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\ReportInterface;
use AppBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Checklist.
 *
 * @ORM\Table(name="checklist")
 * @ORM\Entity()
 */
class Checklist
{
    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\Groups({"report-checklist"})
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="checklist_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @JMS\Type("AppBundle\Entity\Report\Report")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Report\Report", inversedBy="checklist")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     * @JMS\Groups({"checklist-report"})
     */
    private $report;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     *
     * @ORM\Column(name="reporting_period_accurate", type="string", length=3, nullable=true)
     */
    private $reportingPeriodAccurate;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @ORM\Column(name="contact_details_upto_date", type="string", length=3, nullable=true)
     */
    private $contactDetailsUptoDate;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     *
     * @ORM\Column(name="deputy_full_name_accurate_in_casrec", type="string", length=3, nullable=true)
     */
    private $deputyFullNameAccurateInCasrec;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     *
     * @ORM\Column(name="decisions_satisfactory", type="string", length=3, nullable=true)
     */
    private $decisionsSatisfactory;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     *
     * @ORM\Column(name="consultations_satisfactory", type="string", length=3, nullable=true)
     */
    private $consultationsSatisfactory;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     *
     * @ORM\Column(name="care_arrangements", type="string", length=3, nullable=true)
     */
    private $careArrangements;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     *
     * @ORM\Column(name="assets_declared_and_managed", type="string", length=3, nullable=true)
     */
    private $assetsDeclaredAndManaged;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     *
     * @ORM\Column(name="debts_managed", type="string", length=3, nullable=true)
     */
    private $debtsManaged;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     *
     * @ORM\Column(name="open_closing_balances_match", type="string", length=3, nullable=true)
     */
    private $openClosingBalancesMatch;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     *
     * @ORM\Column(name="accounts_balance", type="string", length=3, nullable=true)
     */
    private $accountsBalance;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     *
     * @ORM\Column(name="money_movements_acceptable", type="string", length=3, nullable=true)
     */
    private $moneyMovementsAcceptable;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     *
     * @ORM\Column(name="bond_adequate", type="string", length=3, nullable=true)
     */
    private $bondAdequate;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     *
     * @ORM\Column(name="bond_order_match_casrec", type="string", length=3, nullable=true)
     */
    private $bondOrderMatchCasrec;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     *
     * @ORM\Column(name="future_significant_financial_decisions", type="string", length=3, nullable=true)
     */
    private $futureSignificantFinancialDecisions;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     *
     * @ORM\Column(name="has_deputy_raised_concerns", type="string", length=3, nullable=true)
     */
    private $hasDeputyRaisedConcerns;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     *
     * @ORM\Column(name="case_worker_satisified", type="string", length=3, nullable=true)
     */
    private $caseWorkerSatisified;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report-checklist"})
     * @ORM\Column(name="lodging_summary", type="text", nullable=true)
     */
    private $lodgingSummary;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     *
     * @ORM\Column(name="final_decision", type="string", length=30, nullable=true)
     */
    private $finalDecision;

    /**
     * @var Checlist[]
     *
     * @JMS\Type("array<AppBundle\Entity\Report\ChecklistInformation>")
     * @JMS\Groups({"checklist-information"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\ChecklistInformation", mappedBy="checklist", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdOn"="DESC"})
     */
    private $checklistInformation;

    /**
     * @var string
     *
     * @JMS\Groups({"checklist-information"})
     */
    private $furtherInformationReceived;

    /**
     * Submitted by
     *
     * @var \AppBundle\Entity\User
     *
     * @JMS\Type("AppBundle\Entity\User")
     * @JMS\Groups({"report-checklist"})
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", fetch="EAGER")
     * @ORM\JoinColumn(name="submitted_by", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $submittedBy;

    /**
     * Submitted on
     *
     * @JMS\Type("DateTime")
     * @JMS\Groups({"report-checklist"})
     * @ORM\Column(type="datetime", name="submitted_on", nullable=true)
     */
    protected $submittedOn;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"report-checklist"})
     */
    private $buttonClicked;

    public function __construct(ReportInterface $report)
    {
        $this->setReport($report);
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
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return ReportInterface
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param ReportInterface $report
     * @return $this
     */
    public function setReport(ReportInterface $report)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return string
     */
    public function getReportingPeriodAccurate()
    {
        return $this->reportingPeriodAccurate;
    }

    /**
     * @param string $reportingPeriodAccurate
     * @return $this
     */
    public function setReportingPeriodAccurate($reportingPeriodAccurate)
    {
        $this->reportingPeriodAccurate = $reportingPeriodAccurate;
        return $this;
    }

    /**
     * @return string
     */
    public function getContactDetailsUptoDate()
    {
        return $this->contactDetailsUptoDate;
    }

    /**
     * @param string $contactDetailsUptoDate
     * @return $this
     */
    public function setContactDetailsUptoDate($contactDetailsUptoDate)
    {
        $this->contactDetailsUptoDate = $contactDetailsUptoDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeputyFullNameAccurateInCasrec()
    {
        return $this->deputyFullNameAccurateInCasrec;
    }

    /**
     * @param string $deputyFullNameAccurateInCasrec
     * @return $this
     */
    public function setDeputyFullNameAccurateInCasrec($deputyFullNameAccurateInCasrec)
    {
        $this->deputyFullNameAccurateInCasrec = $deputyFullNameAccurateInCasrec;
        return $this;
    }

    /**
     * @return string
     */
    public function getDecisionsSatisfactory()
    {
        return $this->decisionsSatisfactory;
    }

    /**
     * @param string $decisionsSatisfactory
     * @return $this
     */
    public function setDecisionsSatisfactory($decisionsSatisfactory)
    {
        $this->decisionsSatisfactory = $decisionsSatisfactory;
        return $this;
    }

    /**
     * @return string
     */
    public function getConsultationsSatisfactory()
    {
        return $this->consultationsSatisfactory;
    }

    /**
     * @param string $consultationsSatisfactory
     * @return $this
     */
    public function setConsultationsSatisfactory($consultationsSatisfactory)
    {
        $this->consultationsSatisfactory = $consultationsSatisfactory;
        return $this;
    }

    /**
     * @return string
     */
    public function getCareArrangements()
    {
        return $this->careArrangements;
    }

    /**
     * @param string $careArrangements
     * @return $this
     */
    public function setCareArrangements($careArrangements)
    {
        $this->careArrangements = $careArrangements;
        return $this;
    }

    /**
     * @return string
     */
    public function getAssetsDeclaredAndManaged()
    {
        return $this->assetsDeclaredAndManaged;
    }

    /**
     * @param string $assetsDeclaredAndManaged
     * @return $this
     */
    public function setAssetsDeclaredAndManaged($assetsDeclaredAndManaged)
    {
        $this->assetsDeclaredAndManaged = $assetsDeclaredAndManaged;
        return $this;
    }

    /**
     * @return string
     */
    public function getDebtsManaged()
    {
        return $this->debtsManaged;
    }

    /**
     * @param string $debtsManaged
     * @return $this
     */
    public function setDebtsManaged($debtsManaged)
    {
        $this->debtsManaged = $debtsManaged;
        return $this;
    }

    /**
     * @return string
     */
    public function getOpenClosingBalancesMatch()
    {
        return $this->openClosingBalancesMatch;
    }

    /**
     * @param string $openClosingBalancesMatch
     * @return $this
     */
    public function setOpenClosingBalancesMatch($openClosingBalancesMatch)
    {
        $this->openClosingBalancesMatch = $openClosingBalancesMatch;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccountsBalance()
    {
        return $this->accountsBalance;
    }

    /**
     * @param string $accountsBalance
     * @return $this
     */
    public function setAccountsBalance($accountsBalance)
    {
        $this->accountsBalance = $accountsBalance;
        return $this;
    }

    /**
     * @return string
     */
    public function getMoneyMovementsAcceptable()
    {
        return $this->moneyMovementsAcceptable;
    }

    /**
     * @param string $moneyMovementsAcceptable
     * @return $this
     */
    public function setMoneyMovementsAcceptable($moneyMovementsAcceptable)
    {
        $this->moneyMovementsAcceptable = $moneyMovementsAcceptable;
        return $this;
    }

    /**
     * @return string
     */
    public function getBondAdequate()
    {
        return $this->bondAdequate;
    }

    /**
     * @param string $bondAdequate
     * @return $this
     */
    public function setBondAdequate($bondAdequate)
    {
        $this->bondAdequate = $bondAdequate;
        return $this;
    }

    /**
     * @return string
     */
    public function getBondOrderMatchCasrec()
    {
        return $this->bondOrderMatchCasrec;
    }

    /**
     * @param string $bondOrderMatchCasrec
     * @return $this
     */
    public function setBondOrderMatchCasrec($bondOrderMatchCasrec)
    {
        $this->bondOrderMatchCasrec = $bondOrderMatchCasrec;
        return $this;
    }

    /**
     * @return string
     */
    public function getFutureSignificantFinancialDecisions()
    {
        return $this->futureSignificantFinancialDecisions;
    }

    /**
     * @param string $futureSignificantFinancialDecisions
     * @return $this
     */
    public function setFutureSignificantFinancialDecisions($futureSignificantFinancialDecisions)
    {
        $this->futureSignificantFinancialDecisions = $futureSignificantFinancialDecisions;
        return $this;
    }

    /**
     * @return string
     */
    public function getHasDeputyRaisedConcerns()
    {
        return $this->hasDeputyRaisedConcerns;
    }

    /**
     * @param string $hasDeputyRaisedConcerns
     * @return $this
     */
    public function setHasDeputyRaisedConcerns($hasDeputyRaisedConcerns)
    {
        $this->hasDeputyRaisedConcerns = $hasDeputyRaisedConcerns;
        return $this;
    }

    /**
     * @return string
     */
    public function getCaseWorkerSatisified()
    {
        return $this->caseWorkerSatisified;
    }

    /**
     * @param string $caseWorkerSatisified
     * @return $this
     */
    public function setCaseWorkerSatisified($caseWorkerSatisified)
    {
        $this->caseWorkerSatisified = $caseWorkerSatisified;
        return $this;
    }

    /**
     * @return string
     */
    public function getDecision()
    {
        return $this->decision;
    }

    /**
     * @param string $decision
     * @return $this
     */
    public function setDecision($decision)
    {
        $this->decision = $decision;
        return $this;
    }

    /**
     * @return string
     */
    public function getCaseManagerName()
    {
        return $this->caseManagerName;
    }

    /**
     * @param string $caseManagerName
     * @return $this
     */
    public function setCaseManagerName($caseManagerName)
    {
        $this->caseManagerName = $caseManagerName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLodgingSummary()
    {
        return $this->lodgingSummary;
    }

    /**
     * @param string $lodgingSummary
     * @return $this
     */
    public function setLodgingSummary($lodgingSummary)
    {
        $this->lodgingSummary = $lodgingSummary;
        return $this;
    }

    /**
     * @return string
     */
    public function getFinalDecision()
    {
        return $this->finalDecision;
    }

    /**
     * @param string $finalDecision
     * @return $this
     */
    public function setFinalDecision($finalDecision)
    {
        $this->finalDecision = $finalDecision;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getChecklistInformation()
    {
        return $this->checklistInformation;
    }

    /**
     * @param ArrayCollection $checklistInformation
     */
    public function setChecklistInformation($checklistInformation)
    {
        $this->checklistInformation = $checklistInformation;
    }

    /**
     * @return string
     */
    public function getFurtherInformationReceived()
    {
        return $this->furtherInformationReceived;
    }

    /**
     * @param string $furtherInformation
     */
    public function setFurtherInformationReceived($furtherInformationReceived)
    {
        $this->furtherInformationReceived = $furtherInformationReceived;
    }

    /**
     * @return \AppBundle\Entity\User
     */
    public function getSubmittedBy()
    {
        return $this->submittedBy;
    }

    /**
     * @param \AppBundle\Entity\User $submittedBy
     *
     * @return $this
     */
    public function setSubmittedBy(User $submittedBy)
    {
        $this->submittedBy = $submittedBy;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSubmittedOn()
    {
        return $this->submittedOn;
    }

    /**
     * @param \DateTime $submittedOn
     * @return $this
     */
    public function setSubmittedOn($submittedOn)
    {
        $this->submittedOn = $submittedOn;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getButtonClicked()
    {
        return $this->buttonClicked;
    }

    /**
     * @param string $buttonClicked
     * @return $this
     */
    public function setButtonClicked($buttonClicked)
    {
        $this->buttonClicked = $buttonClicked;
        return $this;
    }
}




