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

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('integer')]
    private ?int $id = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.reportingPeriodAccurate.notBlank', groups: ['submit-common-checklist'])]
    private ?string $reportingPeriodAccurate = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('boolean')]
    #[Assert\NotBlank(message: 'checklist.contactDetailsUptoDate.notBlank', groups: ['submit-common-checklist'])]
    private ?bool $contactDetailsUptoDate = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('boolean')]
    #[Assert\NotBlank(message: 'checklist.deputyFullNameAccurateInSirius.notBlank', groups: ['submit-deputy-fullname-accurate-sirius-checklist'])]
    private ?bool $deputyFullNameAccurateInSirius = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.decisionsSatisfactory.notBlank', groups: ['submit-decisions-checklist'])]
    private ?string $decisionsSatisfactory = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.consultationsSatisfactory.notBlank', groups: ['submit-common-checklist'])]
    private ?string $consultationsSatisfactory = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.careArrangements.notBlank', groups: ['submit-visitsCare-checklist'])]
    private ?string $careArrangements = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.assetsDeclaredAndManaged.notBlank', groups: ['submit-assets-checklist'])]
    private ?string $assetsDeclaredAndManaged = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.debtsManaged.notBlank', groups: ['submit-debts-checklist'])]
    private ?string $debtsManaged = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['report-checklist'])]
    #[Assert\NotBlank(message: 'checklist.yesNoNa', groups: ['submit-clientBenefitsCheck-checklist'])]
    private ?string $clientBenefitsChecked = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.openClosingBalancesMatch.notBlank', groups: ['submit-balance-checklist'])]
    private ?string $openClosingBalancesMatch = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.accountsBalance.notBlank', groups: ['submit-balance-checklist'])]
    private ?string $accountsBalance = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.moneyMovementsAcceptable.notBlank', groups: ['submit-bankAccounts-checklist'])]
    private ?string $moneyMovementsAcceptable = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.deputyChargeAllowedByCourt.notBlank', groups: ['submit-paDeputyExpenses-checklist'])]
    protected ?string $deputyChargeAllowedByCourt = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.satisfiedWithPaExpenses.notBlank', groups: ['submit-paDeputyExpenses-checklist'])]
    protected ?string $satisfiedWithPaExpenses = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.lifestyle.notBlank', groups: ['submit-lifestyle-checklist'])]
    private ?string $satisfiedWithHealthAndLifestyle = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.bondOrderMatchSirius.notBlank', groups: ['submit-bonds-checklist'])]
    private ?string $bondAdequate = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.bondOrderMatchSirius.notBlank', groups: ['submit-bonds-checklist'])]
    private ?string $bondOrderMatchSirius = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.futureSignificantDecisions.notBlank', groups: ['submit-common-checklist'])]
    private ?string $futureSignificantDecisions = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.hasDeputyRaisedConcerns.notBlank', groups: ['submit-common-checklist'])]
    private ?string $hasDeputyRaisedConcerns = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.caseWorkerSatisified.notBlank', groups: ['submit-common-checklist'])]
    private ?string $caseWorkerSatisified = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.paymentsMatchCostCertificate.notBlank', groups: ['submit-profDeputyCosts-checklist'])]
    private ?string $paymentsMatchCostCertificate = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.profCostsReasonableAndProportionate.notBlank', groups: ['submit-profDeputyCosts-checklist'])]
    private ?string $profCostsReasonableAndProportionate = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.hasDeputyOverchargedFromPreviousEstimates.notBlank', groups: ['submit-profDeputyCosts-checklist'])]
    private ?string $hasDeputyOverchargedFromPreviousEstimates = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.nextBillingEstimatesSatisfactory.notBlank', groups: ['submit-profDeputyCostsEstimate-checklist'])]
    private $nextBillingEstimatesSatisfactory;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.lodgingSummary.notBlank', groups: ['submit-common-checklist'])]
    private ?string $lodgingSummary = null;

    #[JMS\Groups(['report-checklist'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'checklist.finalDecision.notBlank', groups: ['submit-common-checklist'])]
    private ?string $finalDecision = null;

    #[JMS\Groups(['checklist-information'])]
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\ChecklistInformation>')]
    private array $checklistInformation = [];

    #[JMS\Groups(['checklist-information'])]
    #[JMS\Type('string')]
    private ?string $furtherInformationReceived = null;

    #[JMS\Type('OPG\Digideps\Frontend\Entity\User')]
    #[JMS\Groups(['checklist-information'])]
    protected ?User $submittedBy = null;

    #[JMS\Type('DateTime')]
    protected ?\DateTime $submittedOn = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['report-checklist'])]
    protected ?string $buttonClicked = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['report-checklist-uuid'])]
    private ?string $uuid = null;

    public function __construct(Report $report)
    {
        $this->setReport($report);

        // DDPB-2272: prefill answers based on report content
        $action = $report->getAction();
        $answer = $action->getDoYouExpectFinancialDecisions();
        if ($answer) {
            $this->setFutureSignificantDecisions($answer);
        }
        $answer = $action->getDoYouHaveConcerns();
        if ($answer) {
            $this->setHasDeputyRaisedConcerns($answer);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getReportingPeriodAccurate(): ?string
    {
        return $this->reportingPeriodAccurate;
    }

    public function setReportingPeriodAccurate(?string $reportingPeriodAccurate): static
    {
        $this->reportingPeriodAccurate = $reportingPeriodAccurate;

        return $this;
    }

    public function getContactDetailsUptoDate(): ?bool
    {
        return $this->contactDetailsUptoDate;
    }

    public function setContactDetailsUptoDate(?bool $contactDetailsUptoDate): static
    {
        $this->contactDetailsUptoDate = $contactDetailsUptoDate;

        return $this;
    }

    public function getDeputyFullNameAccurateInSirius(): ?bool
    {
        return $this->deputyFullNameAccurateInSirius;
    }

    public function setDeputyFullNameAccurateInSirius(?bool $deputyFullNameAccurateInSirius): static
    {
        $this->deputyFullNameAccurateInSirius = $deputyFullNameAccurateInSirius;

        return $this;
    }

    public function getDecisionsSatisfactory(): ?string
    {
        return $this->decisionsSatisfactory;
    }

    public function setDecisionsSatisfactory(?string $decisionsSatisfactory): static
    {
        $this->decisionsSatisfactory = $decisionsSatisfactory;

        return $this;
    }

    public function getConsultationsSatisfactory(): ?string
    {
        return $this->consultationsSatisfactory;
    }

    public function setConsultationsSatisfactory(?string $consultationsSatisfactory): static
    {
        $this->consultationsSatisfactory = $consultationsSatisfactory;

        return $this;
    }

    public function getCareArrangements(): ?string
    {
        return $this->careArrangements;
    }

    public function setCareArrangements(?string $careArrangements): static
    {
        $this->careArrangements = $careArrangements;

        return $this;
    }

    public function getAssetsDeclaredAndManaged(): ?string
    {
        return $this->assetsDeclaredAndManaged;
    }

    public function setAssetsDeclaredAndManaged(?string $assetsDeclaredAndManaged): static
    {
        $this->assetsDeclaredAndManaged = $assetsDeclaredAndManaged;

        return $this;
    }

    public function getDebtsManaged(): ?string
    {
        return $this->debtsManaged;
    }

    public function setDebtsManaged(?string $debtsManaged): static
    {
        $this->debtsManaged = $debtsManaged;

        return $this;
    }

    public function getOpenClosingBalancesMatch(): ?string
    {
        return $this->openClosingBalancesMatch;
    }

    public function setOpenClosingBalancesMatch(?string $openClosingBalancesMatch): static
    {
        $this->openClosingBalancesMatch = $openClosingBalancesMatch;

        return $this;
    }

    public function getAccountsBalance(): ?string
    {
        return $this->accountsBalance;
    }

    public function setAccountsBalance(?string $accountsBalance): static
    {
        $this->accountsBalance = $accountsBalance;

        return $this;
    }

    public function getMoneyMovementsAcceptable(): ?string
    {
        return $this->moneyMovementsAcceptable;
    }

    public function setMoneyMovementsAcceptable(?string $moneyMovementsAcceptable): static
    {
        $this->moneyMovementsAcceptable = $moneyMovementsAcceptable;

        return $this;
    }

    public function getDeputyChargeAllowedByCourt(): ?string
    {
        return $this->deputyChargeAllowedByCourt;
    }

    public function setDeputyChargeAllowedByCourt(?string $deputyChargeAllowedByCourt): static
    {
        $this->deputyChargeAllowedByCourt = $deputyChargeAllowedByCourt;

        return $this;
    }

    public function getSatisfiedWithPaExpenses(): ?string
    {
        return $this->satisfiedWithPaExpenses;
    }

    public function setSatisfiedWithPaExpenses(?string $satisfiedWithPaExpenses): static
    {
        $this->satisfiedWithPaExpenses = $satisfiedWithPaExpenses;

        return $this;
    }

    public function getSatisfiedWithHealthAndLifestyle(): ?string
    {
        return $this->satisfiedWithHealthAndLifestyle;
    }

    public function setSatisfiedWithHealthAndLifestyle(?string $satisfiedWithHealthAndLifestyle): static
    {
        $this->satisfiedWithHealthAndLifestyle = $satisfiedWithHealthAndLifestyle;

        return $this;
    }

    public function getBondAdequate(): ?string
    {
        return $this->bondAdequate;
    }

    public function setBondAdequate(?string $bondAdequate): static
    {
        $this->bondAdequate = $bondAdequate;

        return $this;
    }

    public function getBondOrderMatchSirius(): ?string
    {
        return $this->bondOrderMatchSirius;
    }

    public function setBondOrderMatchSirius(?string $bondOrderMatchSirius): static
    {
        $this->bondOrderMatchSirius = $bondOrderMatchSirius;

        return $this;
    }

    public function getFutureSignificantDecisions(): ?string
    {
        return $this->futureSignificantDecisions;
    }

    public function setFutureSignificantDecisions(?string $futureSignificantDecisions): static
    {
        $this->futureSignificantDecisions = $futureSignificantDecisions;

        return $this;
    }

    public function getHasDeputyRaisedConcerns(): ?string
    {
        return $this->hasDeputyRaisedConcerns;
    }

    public function setHasDeputyRaisedConcerns(?string $hasDeputyRaisedConcerns): static
    {
        $this->hasDeputyRaisedConcerns = $hasDeputyRaisedConcerns;

        return $this;
    }

    public function getCaseWorkerSatisified(): ?string
    {
        return $this->caseWorkerSatisified;
    }

    public function setCaseWorkerSatisified(?string $caseWorkerSatisified): static
    {
        $this->caseWorkerSatisified = $caseWorkerSatisified;

        return $this;
    }

    public function getPaymentsMatchCostCertificate(): ?string
    {
        return $this->paymentsMatchCostCertificate;
    }

    public function setPaymentsMatchCostCertificate(?string $paymentsMatchCostCertificate): static
    {
        $this->paymentsMatchCostCertificate = $paymentsMatchCostCertificate;

        return $this;
    }

    public function getProfCostsReasonableAndProportionate(): ?string
    {
        return $this->profCostsReasonableAndProportionate;
    }

    public function setProfCostsReasonableAndProportionate(?string $profCostsReasonableAndProportionate): static
    {
        $this->profCostsReasonableAndProportionate = $profCostsReasonableAndProportionate;

        return $this;
    }

    public function getHasDeputyOverchargedFromPreviousEstimates(): ?string
    {
        return $this->hasDeputyOverchargedFromPreviousEstimates;
    }

    public function setHasDeputyOverchargedFromPreviousEstimates(
        ?string $hasDeputyOverchargedFromPreviousEstimates
    ): static {
        $this->hasDeputyOverchargedFromPreviousEstimates = $hasDeputyOverchargedFromPreviousEstimates;

        return $this;
    }

    public function getNextBillingEstimatesSatisfactory(): ?string
    {
        return $this->nextBillingEstimatesSatisfactory;
    }

    public function setNextBillingEstimatesSatisfactory(
        ?string $nextBillingEstimatesSatisfactory
    ): static {
        $this->nextBillingEstimatesSatisfactory = $nextBillingEstimatesSatisfactory;

        return $this;
    }

    public function getLodgingSummary(): ?string
    {
        return $this->lodgingSummary;
    }

    public function setLodgingSummary(?string $lodgingSummary): static
    {
        $this->lodgingSummary = $lodgingSummary;

        return $this;
    }

    public function getFinalDecision(): ?string
    {
        return $this->finalDecision;
    }

    public function setFinalDecision(?string $finalDecision): static
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
    public function setChecklistInformation(array $checklistInformation): static
    {
        $this->checklistInformation = $checklistInformation;

        return $this;
    }

    public function getFurtherInformationReceived(): ?string
    {
        return $this->furtherInformationReceived;
    }

    public function setFurtherInformationReceived(?string $furtherInformationReceived): static
    {
        $this->furtherInformationReceived = $furtherInformationReceived;

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

    public function getSubmittedOn(): ?\DateTime
    {
        return $this->submittedOn;
    }

    public function setSubmittedOn(?\DateTime $submittedOn): static
    {
        $this->submittedOn = $submittedOn;

        return $this;
    }

    public function getButtonClicked(): ?string
    {
        return $this->buttonClicked;
    }

    public function setButtonClicked(?string $buttonClicked): static
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
