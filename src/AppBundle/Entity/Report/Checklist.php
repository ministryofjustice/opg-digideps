<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Report\Traits\HasReportTrait;
use AppBundle\Entity\ReportInterface;
use AppBundle\Entity\Traits\ModifyAudit;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Checklist.
 *
 */
class Checklist
{
    use HasReportTrait;
    use ModifyAudit;

    /**
     * @var int
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("integer")
     */
    private $id;


    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     * @Assert\NotBlank(message="checklist.reportingPeriodAccurate.notBlank", groups={"submit-common-checklist"})
     */
    private $reportingPeriodAccurate;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("boolean")
     * @Assert\NotBlank(message="checklist.contactDetailsUptoDate.notBlank", groups={"submit-common-checklist"})
     */
    private $contactDetailsUptoDate;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("boolean")
     * @Assert\NotBlank(message="checklist.deputyFullNameAccurateinCasrec.notBlank", groups={"submit-common-checklist"})
     */
    private $deputyFullNameAccurateInCasrec;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     * @Assert\NotBlank(message="checklist.decisionsSatisfactory.notBlank", groups={"submit-decisions-checklist"})
     */
    private $decisionsSatisfactory;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     * @Assert\NotBlank(message="checklist.consultationsSatisfactory.notBlank", groups={"submit-common-checklist"})
     */
    private $consultationsSatisfactory;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     * @Assert\NotBlank(message="checklist.careArrangements.notBlank", groups={"submit-visitsCare-checklist"})
     */
    private $careArrangements;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     * @Assert\NotBlank(message="checklist.assetsDeclaredAndManaged.notBlank", groups={"submit-assets-checklist"})
     */
    private $assetsDeclaredAndManaged;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     * @Assert\NotBlank(message="checklist.debtsManaged.notBlank", groups={"submit-debts-checklist"})
     */
    private $debtsManaged;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     * @Assert\NotBlank(message="checklist.openClosingBalancesMatch.notBlank", groups={"submit-balance-checklist"})
     */
    private $openClosingBalancesMatch;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     * @Assert\NotBlank(message="checklist.accountsBalance.notBlank", groups={"submit-balance-checklist"})
     */
    private $accountsBalance;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     * @Assert\NotBlank(message="checklist.moneyMovementsAcceptable.notBlank", groups={"submit-bankAccounts-checklist"})
     */
    private $moneyMovementsAcceptable;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     * @Assert\NotBlank(message="checklist.satisfiedWithPaExpenses.notBlank", groups={"submit-paDeputyExpenses-checklist"})
     */
    protected $satisfiedWithPaExpenses;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     * @Assert\NotBlank(message="checklist.lifestyle.notBlank", groups={"submit-lifestyle-checklist"})
     */
    private $satisfiedWithHealthAndLifestyle;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     * @Assert\NotBlank(message="checklist.bondOrderMatchCasrec.notBlank", groups={"submit-bonds-checklist"})
     */
    private $bondAdequate;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     * @Assert\NotBlank(message="checklist.bondOrderMatchCasrec.notBlank", groups={"submit-bonds-checklist"})
     */
    private $bondOrderMatchCasrec;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     * @Assert\NotBlank(message="checklist.futureSignificantFinancialDecisions.notBlank", groups={"submit-common-checklist"})
     */
    private $futureSignificantFinancialDecisions;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     * @Assert\NotBlank(message="checklist.hasDeputyRaisedConcerns.notBlank", groups={"submit-common-checklist"})
     */
    private $hasDeputyRaisedConcerns;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     * @Assert\NotBlank(message="checklist.caseWorkerSatisified.notBlank", groups={"submit-common-checklist"})
     */
    private $caseWorkerSatisified;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     * @Assert\NotBlank(message="checklist.lodgingSummary.notBlank", groups={"submit-common-checklist"})
     */
    private $lodgingSummary;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     * @Assert\NotBlank(message="checklist.finalDecision.notBlank", groups={"submit-common-checklist"})
     */
    private $finalDecision;

    /**
     * @var CheclistInformation[]
     *
     * @JMS\Groups({"checklist-information"})
     *
     * @JMS\Type("array<AppBundle\Entity\Report\ChecklistInformation>")
     */
    private $checklistInformation;

    /**
     * @var string
     *
     * @JMS\Groups({"checklist-information"})
     * @JMS\Type("string")
     */
    private $furtherInformationReceived;

    /**
     * Submitted by
     *
     * @JMS\Type("AppBundle\Entity\User")
     * @var \AppBundle\Entity\User
     *
     */
    protected $submittedBy;

    /**
     * Submitted on
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     *
     */
    protected $submittedOn;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"report-checklist"})
     */
    protected $buttonClicked;

    /**
     * Checklist constructor.
     *
     * @param ReportInterface $report
     *
     */
    public function __construct(ReportInterface $report)
    {
        $this->setReport($report);

        // DDPB-2272: prefill answers based on report content
        if ($report instanceof Report) {
            $action = $report->getAction();
            if ($answer = $action->getDoYouExpectFinancialDecisions()) {
                $this->setFutureSignificantFinancialDecisions($answer);
            }
            if ($answer = $action->getDoYouHaveConcerns()) {
                $this->setHasDeputyRaisedConcerns($answer);
            }
        }

    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  int   $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @param  string $reportingPeriodAccurate
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
     * @param  string $contactDetailsUptoDate
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
     * @param  string $deputyFullNameAccurateInCasrec
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
     * @param  string $decisionsSatisfactory
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
     * @param  string $consultationsSatisfactory
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
     * @param  string $careArrangements
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
     * @param  string $assetsDeclaredAndManaged
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
     * @param  string $debtsManaged
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
     * @param  string $openClosingBalancesMatch
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
     * @param  string $accountsBalance
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
     * @param  string $moneyMovementsAcceptable
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
    public function getSatisfiedWithPaExpenses()
    {
        return $this->satisfiedWithPaExpenses;
    }

    /**
     * @param string $satisfiedWithPaExpenses
     * @return $this
     */
    public function setSatisfiedWithPaExpenses($satisfiedWithPaExpenses)
    {
        $this->satisfiedWithPaExpenses = $satisfiedWithPaExpenses;
        return $this;
    }

    /**
     * @return string
     */
    public function getSatisfiedWithHealthAndLifestyle()
    {
        return $this->satisfiedWithHealthAndLifestyle;
    }

    /**
     * @param string $satisfiedWithPaExpenses
     * @return $this
     */
    public function setSatisfiedWithHealthAndLifestyle($satisfiedWithHealthAndLifestyle)
    {
        $this->satisfiedWithHealthAndLifestyle = $satisfiedWithHealthAndLifestyle;
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
     * @param  string $bondAdequate
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
     * @param  string $bondOrderMatchCasrec
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
     * @param  string $futureSignificantFinancialDecisions
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
     * @param  string $hasDeputyRaisedConcerns
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
     * @param  string $caseWorkerSatisified
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
     * @param  string $decision
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
     * @param  string $caseManagerName
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
     * @param  string $lodgingSummary
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
     * @param  string $finalDecision
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
     * @param  \AppBundle\Entity\User $submittedBy
     * @return $this
     */
    public function setSubmittedBy($submittedBy)
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
     * @param  \DateTime $submittedOn
     * @return $this
     */
    public function setSubmittedOn($submittedOn)
    {
        $this->submittedOn = $submittedOn;
        return $this;
    }

    /**
     * @return string
     */
    public function getButtonClicked()
    {
        return $this->buttonClicked;
    }

    /**
     * @param  string $buttonClicked
     * @return $this
     */
    public function setButtonClicked($buttonClicked)
    {
        $this->buttonClicked = $buttonClicked;
        return $this;
    }
}
