<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\SynchronisableInterface;
use OPG\Digideps\Backend\Entity\SynchronisableTrait;
use OPG\Digideps\Backend\Entity\Traits\ModifyAudit;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Repository\ChecklistRepository;

#[ORM\Table(name: 'checklist')]
#[ORM\Entity(repositoryClass: ChecklistRepository::class)]
class Checklist implements SynchronisableInterface
{
    use ModifyAudit;
    use SynchronisableTrait;

    #[JMS\Type('integer')]
    #[JMS\Groups(['report-checklist', 'checklist-id'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'checklist_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[JMS\Type('OPG\Digideps\Backend\Entity\Report\Report')]
    #[JMS\Groups(['checklist-report'])]
    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\OneToOne(inversedBy: 'checklist', targetEntity: Report::class)]
    private Report $report;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'reporting_period_accurate', type: 'string', length: 3, nullable: true)]
    private ?string $reportingPeriodAccurate = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'contact_details_upto_date', type: 'string', length: 3, nullable: true)]
    private ?string $contactDetailsUptoDate = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'deputy_full_name_accurate_in_sirius', type: 'string', length: 3, nullable: true)]
    private ?string $deputyFullNameAccurateInSirius = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'decisions_satisfactory', type: 'string', length: 3, nullable: true)]
    private ?string $decisionsSatisfactory = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'consultations_satisfactory', type: 'string', length: 3, nullable: true)]
    private ?string $consultationsSatisfactory = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'care_arrangements', type: 'string', length: 3, nullable: true)]
    private ?string $careArrangements = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'assets_declared_and_managed', type: 'string', length: 3, nullable: true)]
    private ?string $assetsDeclaredAndManaged = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'debts_managed', type: 'string', length: 3, nullable: true)]
    private ?string $debtsManaged = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'open_closing_balances_match', type: 'string', length: 3, nullable: true)]
    private ?string $openClosingBalancesMatch = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'accounts_balance', type: 'string', length: 3, nullable: true)]
    private ?string $accountsBalance = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'money_movements_acceptable', type: 'string', length: 3, nullable: true)]
    private ?string $moneyMovementsAcceptable = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'deputy_charge_allowed_by_court', type: 'string', length: 3, nullable: true)]
    private ?string $deputyChargeAllowedByCourt = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'satisfied_with_pa_expenses', type: 'string', length: 3, nullable: true)]
    private ?string $satisfiedWithPaExpenses = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'satisfied_with_health_and_lifestyle', type: 'string', length: 3, nullable: true)]
    private ?string $satisfiedWithHealthAndLifestyle = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'bond_adequate', type: 'string', length: 3, nullable: true)]
    private ?string $bondAdequate = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'bond_order_match_sirius', type: 'string', length: 3, nullable: true)]
    private ?string $bondOrderMatchSirius = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'future_significant_decisions', type: 'string', length: 3, nullable: true)]
    private ?string $futureSignificantDecisions = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'has_deputy_raised_concerns', type: 'string', length: 3, nullable: true)]
    private ?string $hasDeputyRaisedConcerns = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'case_worker_satisified', type: 'string', length: 3, nullable: true)]
    private ?string $caseWorkerSatisified = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'payments_match_cost_certificate', type: 'string', length: 3, nullable: true)]
    private ?string $paymentsMatchCostCertificate = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'prof_costs_reasonable_and_proportionate', type: 'string', length: 3, nullable: true)]
    private ?string $profCostsReasonableAndProportionate = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'has_deputy_overcharged_from_previous_estimates', type: 'string', length: 3, nullable: true)]
    private ?string $hasDeputyOverchargedFromPreviousEstimates = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'next_billing_estimate_satisfactory', type: 'string', length: 3, nullable: true)]
    private ?string $nextBillingEstimatesSatisfactory = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'lodging_summary', type: 'text', nullable: true)]
    private ?string $lodgingSummary = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'final_decision', type: 'string', length: 30, nullable: true)]
    private ?string $finalDecision = null;

    /**
     * @var Collection<int, ChecklistInformation>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\ChecklistInformation>')]
    #[JMS\Groups(['checklist-information'])]
    #[ORM\OneToMany(mappedBy: 'checklist', targetEntity: ChecklistInformation::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdOn' => 'DESC'])]
    private Collection $checklistInformation;

    #[JMS\Groups(['checklist-information'])]
    private ?string $furtherInformationReceived = null;

    #[JMS\Type('OPG\Digideps\Backend\Entity\User')]
    #[JMS\Groups(['report-checklist'])]
    #[ORM\JoinColumn(name: 'submitted_by', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER')]
    private ?User $submittedBy = null;

    #[JMS\Type('DateTime')]
    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'submitted_on', type: 'datetime', nullable: true)]
    private ?\DateTime $submittedOn = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['report-checklist'])]
    private ?string $buttonClicked = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['report-submission', 'report-checklist', 'checklist-uuid'])]
    #[ORM\Column(name: 'opg_uuid', type: 'string', length: 36, nullable: true)]
    private ?string $uuid = null;

    #[JMS\Groups(['report-checklist'])]
    #[ORM\Column(name: 'client_benefits_checked', type: 'string', length: 3, nullable: true)]
    private ?string $clientBenefitsChecked = null;

    public function __construct(Report $report)
    {
        $this->checklistInformation = new ArrayCollection();
        $this->report = $report;
    }

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function setId(int $id): static
    {
        if ($this->id === null) {
            $this->id = $id;
        } elseif ($id === 0) {
            throw new \DomainException('You may not set the id of an entity to zero.');
        } else {
            throw new \LogicException('You may not set the id of an entity more than once.');
        }

        return $this;
    }

    public function getReport(): Report
    {
        return $this->report;
    }

    public function setReport(Report $report): static
    {
        $this->report = $report;

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

    public function getContactDetailsUptoDate(): ?string
    {
        return $this->contactDetailsUptoDate;
    }

    public function setContactDetailsUptoDate(?string $contactDetailsUptoDate): static
    {
        $this->contactDetailsUptoDate = $contactDetailsUptoDate;

        return $this;
    }

    public function getDeputyFullNameAccurateInSirius(): ?string
    {
        return $this->deputyFullNameAccurateInSirius;
    }

    public function setDeputyFullNameAccurateInSirius(?string $deputyFullNameAccurateInSirius): static
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

    public function setDeputyChargeAllowedByCourt(?string $deputyChargeAllowedByCourt): void
    {
        $this->deputyChargeAllowedByCourt = $deputyChargeAllowedByCourt;
    }

    public function getSatisfiedWithPaExpenses(): ?string
    {
        return $this->satisfiedWithPaExpenses;
    }

    public function setSatisfiedWithPaExpenses(?string $satisfiedWithPaExpenses): void
    {
        $this->satisfiedWithPaExpenses = $satisfiedWithPaExpenses;
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

    public function setHasDeputyOverchargedFromPreviousEstimates(?string $hasDeputyOverchargedFromPreviousEstimates): static
    {
        $this->hasDeputyOverchargedFromPreviousEstimates = $hasDeputyOverchargedFromPreviousEstimates;

        return $this;
    }

    public function getNextBillingEstimatesSatisfactory(): ?string
    {
        return $this->nextBillingEstimatesSatisfactory;
    }

    public function setNextBillingEstimatesSatisfactory(?string $nextBillingEstimatesSatisfactory): static
    {
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
     * @return Collection<int, ChecklistInformation>
     */
    public function getChecklistInformation(): Collection
    {
        return $this->checklistInformation;
    }

    /**
     * @param Collection<int, ChecklistInformation> $checklistInformation
     */
    public function setChecklistInformation(Collection $checklistInformation): static
    {
        $this->checklistInformation = $checklistInformation;

        return $this;
    }

    public function getFurtherInformationReceived(): ?string
    {
        return $this->furtherInformationReceived;
    }

    public function setFurtherInformationReceived(?string $furtherInformationReceived): void
    {
        $this->furtherInformationReceived = $furtherInformationReceived;
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

    public function setClientBenefitsChecked(?string $clientBenefitsChecked): Checklist
    {
        $this->clientBenefitsChecked = $clientBenefitsChecked;

        return $this;
    }
}
