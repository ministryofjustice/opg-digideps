<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use OPG\Digideps\Frontend\Entity\Report\Traits\HasReportTrait;
use OPG\Digideps\Frontend\Entity\SynchronisableInterface;
use OPG\Digideps\Frontend\Entity\SynchronisableTrait;
use OPG\Digideps\Frontend\Entity\Traits\ModifyAudit;
use OPG\Digideps\Frontend\Entity\User;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Checklist implements SynchronisableInterface
{
    use HasReportTrait;
    use ModifyAudit;
    use SynchronisableTrait;

    /**
     * @var int
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('integer')]
    private $id;

    /**
     * @var string
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.reportingPeriodAccurate.notBlank', groups: ['submit-common-checklist'])]
    private $reportingPeriodAccurate;

    /**
     * @var bool
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('boolean')]
    #[Assert\NotBlank(message: 'checklist.contactDetailsUptoDate.notBlank', groups: ['submit-common-checklist'])]
    private $contactDetailsUptoDate;

    /**
     * @var bool
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('boolean')]
    #[Assert\NotBlank(message: 'checklist.deputyFullNameAccurateInSirius.notBlank', groups: ['submit-deputy-fullname-accurate-sirius-checklist'])]
    private $deputyFullNameAccurateInSirius;

    /**
     * @var string
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.decisionsSatisfactory.notBlank', groups: ['submit-decisions-checklist'])]
    private $decisionsSatisfactory;

    /**
     * @var string
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.consultationsSatisfactory.notBlank', groups: ['submit-common-checklist'])]
    private $consultationsSatisfactory;

    /**
     * @var string
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.careArrangements.notBlank', groups: ['submit-visitsCare-checklist'])]
    private $careArrangements;

    /**
     * @var string
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.assetsDeclaredAndManaged.notBlank', groups: ['submit-assets-checklist'])]
    private $assetsDeclaredAndManaged;

    /**
     * @var string
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.debtsManaged.notBlank', groups: ['submit-debts-checklist'])]
    private $debtsManaged;

    /**
     * @var string|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-checklist'])]
    #[Assert\NotBlank(message: 'checklist.yesNoNa', groups: ['submit-clientBenefitsCheck-checklist'])]
    private $clientBenefitsChecked;

    /**
     * @var string
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.openClosingBalancesMatch.notBlank', groups: ['submit-balance-checklist'])]
    private $openClosingBalancesMatch;

    /**
     * @var string
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.accountsBalance.notBlank', groups: ['submit-balance-checklist'])]
    private $accountsBalance;

    /**
     * @var string
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.moneyMovementsAcceptable.notBlank', groups: ['submit-bankAccounts-checklist'])]
    private $moneyMovementsAcceptable;

    /**
     * @var string
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.deputyChargeAllowedByCourt.notBlank', groups: ['submit-paDeputyExpenses-checklist'])]
    protected $deputyChargeAllowedByCourt;

    /**
     * @var string
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.satisfiedWithPaExpenses.notBlank', groups: ['submit-paDeputyExpenses-checklist'])]
    protected $satisfiedWithPaExpenses;

    /**
     * @var string
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.lifestyle.notBlank', groups: ['submit-lifestyle-checklist'])]
    private $satisfiedWithHealthAndLifestyle;

    /**
     * @var string
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.bondOrderMatchSirius.notBlank', groups: ['submit-bonds-checklist'])]
    private $bondAdequate;

    /**
     * @var string
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.bondOrderMatchSirius.notBlank', groups: ['submit-bonds-checklist'])]
    private $bondOrderMatchSirius;

    /**
     * @var string
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.futureSignificantDecisions.notBlank', groups: ['submit-common-checklist'])]
    private $futureSignificantDecisions;

    /**
     * @var string
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.hasDeputyRaisedConcerns.notBlank', groups: ['submit-common-checklist'])]
    private $hasDeputyRaisedConcerns;

    /**
     * @var string
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.caseWorkerSatisified.notBlank', groups: ['submit-common-checklist'])]
    private $caseWorkerSatisified;

    /**
     * @var ?string
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.paymentsMatchCostCertificate.notBlank', groups: ['submit-profDeputyCosts-checklist'])]
    private $paymentsMatchCostCertificate;

    /**
     * @var ?string
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.profCostsReasonableAndProportionate.notBlank', groups: ['submit-profDeputyCosts-checklist'])]
    private $profCostsReasonableAndProportionate;

    /**
     * @var ?string
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.hasDeputyOverchargedFromPreviousEstimates.notBlank', groups: ['submit-profDeputyCosts-checklist'])]
    private $hasDeputyOverchargedFromPreviousEstimates;

    /**
     * @var string
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.nextBillingEstimatesSatisfactory.notBlank', groups: ['submit-profDeputyCostsEstimate-checklist'])]
    private $nextBillingEstimatesSatisfactory;

    /**
     * @var string
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.lodgingSummary.notBlank', groups: ['submit-common-checklist'])]
    private $lodgingSummary;

    /**
     * @var string
     */
    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.finalDecision.notBlank', groups: ['submit-common-checklist'])]
    private $finalDecision;

    /**
     * @var ChecklistInformation[]
     */
    #[JMS\Groups(['checklist-information'])]
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\ChecklistInformation>')]
    private $checklistInformation = [];

    /**
     * @var string
     */
    #[JMS\Groups(['checklist-information'])]
    #[JMS\Type('string')]
    private $furtherInformationReceived;

    /**
     * @var User
     */
    #[JMS\Type('OPG\Digideps\Frontend\Entity\User')]
    #[JMS\Groups(['checklist-information'])]
    protected $submittedBy;

    /**
     * @var \DateTime
     */
    #[JMS\Type('DateTime')]
    protected $submittedOn;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-checklist'])]
    protected $buttonClicked;

    /**
     * @var ?string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-checklist-uuid'])]
    private $uuid;

    public function __construct(Report $report)
    {
        $this->setReport($report);

        // DDPB-2272: prefill answers based on report content
        $action = $report->getAction();
        if ($answer = $action->getDoYouExpectFinancialDecisions()) {
            $this->setFutureSignificantDecisions($answer);
        }
        if ($answer = $action->getDoYouHaveConcerns()) {
            $this->setHasDeputyRaisedConcerns($answer);
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
    public function getReportingPeriodAccurate()
    {
        return $this->reportingPeriodAccurate;
    }

    /**
     * @param string $reportingPeriodAccurate
     */
    public function setReportingPeriodAccurate($reportingPeriodAccurate): static
    {
        $this->reportingPeriodAccurate = $reportingPeriodAccurate;

        return $this;
    }

    /**
     * @return bool
     */
    public function getContactDetailsUptoDate()
    {
        return $this->contactDetailsUptoDate;
    }

    /**
     * @param bool $contactDetailsUptoDate
     */
    public function setContactDetailsUptoDate($contactDetailsUptoDate): static
    {
        $this->contactDetailsUptoDate = $contactDetailsUptoDate;

        return $this;
    }

    /**
     * @return bool
     */
    public function getDeputyFullNameAccurateInSirius()
    {
        return $this->deputyFullNameAccurateInSirius;
    }

    /**
     * @param bool $deputyFullNameAccurateInSirius
     */
    public function setDeputyFullNameAccurateInSirius($deputyFullNameAccurateInSirius): static
    {
        $this->deputyFullNameAccurateInSirius = $deputyFullNameAccurateInSirius;

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
     */
    public function setDecisionsSatisfactory($decisionsSatisfactory): static
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
     */
    public function setConsultationsSatisfactory($consultationsSatisfactory): static
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
     */
    public function setCareArrangements($careArrangements): static
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
     */
    public function setAssetsDeclaredAndManaged($assetsDeclaredAndManaged): static
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
     */
    public function setDebtsManaged($debtsManaged): static
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
     */
    public function setOpenClosingBalancesMatch($openClosingBalancesMatch): static
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
     */
    public function setAccountsBalance($accountsBalance): static
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
     */
    public function setMoneyMovementsAcceptable($moneyMovementsAcceptable): static
    {
        $this->moneyMovementsAcceptable = $moneyMovementsAcceptable;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeputyChargeAllowedByCourt()
    {
        return $this->deputyChargeAllowedByCourt;
    }

    /**
     * @param string $deputyChargeAllowedByCourt
     */
    public function setDeputyChargeAllowedByCourt($deputyChargeAllowedByCourt): static
    {
        $this->deputyChargeAllowedByCourt = $deputyChargeAllowedByCourt;

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
     */
    public function setSatisfiedWithPaExpenses($satisfiedWithPaExpenses): static
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
     * @param string $satisfiedWithHealthAndLifestyle
     */
    public function setSatisfiedWithHealthAndLifestyle($satisfiedWithHealthAndLifestyle): static
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
     * @param string $bondAdequate
     */
    public function setBondAdequate($bondAdequate): static
    {
        $this->bondAdequate = $bondAdequate;

        return $this;
    }

    /**
     * @return string
     */
    public function getBondOrderMatchSirius()
    {
        return $this->bondOrderMatchSirius;
    }

    /**
     * @param string $bondOrderMatchSirius
     */
    public function setBondOrderMatchSirius($bondOrderMatchSirius): static
    {
        $this->bondOrderMatchSirius = $bondOrderMatchSirius;

        return $this;
    }

    /**
     * @return string
     */
    public function getFutureSignificantDecisions()
    {
        return $this->futureSignificantDecisions;
    }

    /**
     * @param string $futureSignificantDecisions
     */
    public function setFutureSignificantDecisions($futureSignificantDecisions): static
    {
        $this->futureSignificantDecisions = $futureSignificantDecisions;

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
     */
    public function setHasDeputyRaisedConcerns($hasDeputyRaisedConcerns): static
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
     */
    public function setCaseWorkerSatisified($caseWorkerSatisified): static
    {
        $this->caseWorkerSatisified = $caseWorkerSatisified;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getPaymentsMatchCostCertificate()
    {
        return $this->paymentsMatchCostCertificate;
    }

    /**
     * @param ?string $paymentsMatchCostCertificate
     */
    public function setPaymentsMatchCostCertificate($paymentsMatchCostCertificate): static
    {
        $this->paymentsMatchCostCertificate = $paymentsMatchCostCertificate;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getProfCostsReasonableAndProportionate()
    {
        return $this->profCostsReasonableAndProportionate;
    }

    /**
     * @param ?string $profCostsReasonableAndProportionate
     */
    public function setProfCostsReasonableAndProportionate($profCostsReasonableAndProportionate): static
    {
        $this->profCostsReasonableAndProportionate = $profCostsReasonableAndProportionate;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getHasDeputyOverchargedFromPreviousEstimates()
    {
        return $this->hasDeputyOverchargedFromPreviousEstimates;
    }

    /**
     * @param ?string $hasDeputyOverchargedFromPreviousEstimates
     */
    public function setHasDeputyOverchargedFromPreviousEstimates($hasDeputyOverchargedFromPreviousEstimates): static
    {
        $this->hasDeputyOverchargedFromPreviousEstimates = $hasDeputyOverchargedFromPreviousEstimates;

        return $this;
    }

    /**
     * @return string
     */
    public function getNextBillingEstimatesSatisfactory()
    {
        return $this->nextBillingEstimatesSatisfactory;
    }

    /**
     * @param string $nextBillingEstimatesSatisfactory
     */
    public function setNextBillingEstimatesSatisfactory($nextBillingEstimatesSatisfactory): static
    {
        $this->nextBillingEstimatesSatisfactory = $nextBillingEstimatesSatisfactory;

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
     */
    public function setLodgingSummary($lodgingSummary): static
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
     */
    public function setFinalDecision($finalDecision): static
    {
        $this->finalDecision = $finalDecision;

        return $this;
    }

    /**
     * @return ChecklistInformation[]
     */
    public function getChecklistInformation(): array
    {
        return $this->checklistInformation;
    }

    /**
     * @param ChecklistInformation[] $checklistInformation
     */
    public function setChecklistInformation(array $checklistInformation)
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
     * @param string $furtherInformationReceived
     */
    public function setFurtherInformationReceived($furtherInformationReceived): static
    {
        $this->furtherInformationReceived = $furtherInformationReceived;

        return $this;
    }

    /**
     * @return User
     */
    public function getSubmittedBy()
    {
        return $this->submittedBy;
    }

    /**
     * @param User $submittedBy
     */
    public function setSubmittedBy($submittedBy): static
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
     */
    public function setSubmittedOn($submittedOn): static
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
     * @param string $buttonClicked
     */
    public function setButtonClicked($buttonClicked): static
    {
        $this->buttonClicked = $buttonClicked;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getClientBenefitsChecked(): ?string
    {
        return $this->clientBenefitsChecked;
    }

    public function setClientBenefitsChecked(?string $clientBenefitsChecked): static
    {
        $this->clientBenefitsChecked = $clientBenefitsChecked;

        return $this;
    }
}
