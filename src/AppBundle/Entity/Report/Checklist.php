<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\ReportInterface;
use AppBundle\Entity\Report\Traits\HasReportTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Checklist.
 *
 */
class Checklist
{
    use HasReportTrait;

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
     */
    private $reportingPeriodAccurate;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("boolean")
     */
    private $contactDetailsUptoDate;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("boolean")
     */
    private $deputyFullNameAccurateinCasrec;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     */
    private $decisionsSatisfactory;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     */
    private $consultationsSatisfactory;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     */
    private $careArrangements;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     */
    private $assetsDeclaredAndManaged;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     */
    private $debtsManaged;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     */
    private $openClosingBalancesMatch;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     */
    private $accountsBalance;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     */
    private $moneyMovementsAcceptable;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     */
    private $bondAdequate;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     */
    private $bondOrderMatchCasrec;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     */
    private $futureSignificantFinancialDecisions;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     */
    private $hasDeputyRaisedConcerns;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     */
    private $caseWorkerSatisified;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     */
    private $decision;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     */
    private $caseManagerName;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
     */
    private $lodgingSummary;

    /**
     * @var string
     *
     * @JMS\Groups({"report-checklist"})
     * @JMS\Type("string")
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
     * Checklist constructor.
     *
     * @param ReportInterface $report
     *
     */
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
    public function getDeputyFullNameAccurateinCasrec()
    {
        return $this->deputyFullNameAccurateinCasrec;
    }

    /**
     * @param string $deputyFullNameAccurateinCasrec
     * @return $this
     */
    public function setDeputyFullNameAccurateinCasrec($deputyFullNameAccurateinCasrec)
    {
        $this->deputyFullNameAccurateinCasrec = $deputyFullNameAccurateinCasrec;
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
}




