<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Report\ProfServiceFee;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportProfServiceFeesTrait
{
    /**
     * @var ?string 'yes'|'no'|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'current-prof-payments-received'])]
    #[Assert\NotBlank(message: 'common.yesnochoice.notBlank', groups: ['current-prof-payments-received'])]
    private ?string $currentProfPaymentsReceived = null;

    /**
     * @var ?string 'yes'|'no'|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'report-prof-estimate-fees'])]
    #[Assert\NotBlank(message: 'profServiceFee.estimates.previousProfFeesEstimateGiven.notBlank', groups: ['previous-prof-fees-estimate-choice'])]
    private ?string $previousProfFeesEstimateGiven;

    /**
     * @var ?string 'yes'|'no'|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'report-prof-estimate-fees'])]
    private ?string $profFeesEstimateSccoReason = null;

    /**
     * @var array<ProfServiceFee>
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\ProfServiceFee>')]
    #[JMS\Groups(['report-prof-service-fees'])]
    private array $profServiceFees = [];

    public function getCurrentProfPaymentsReceived(): ?string
    {
        return $this->currentProfPaymentsReceived;
    }

    public function setCurrentProfPaymentsReceived(?string $currentProfPaymentsReceived): static
    {
        $this->currentProfPaymentsReceived = $currentProfPaymentsReceived;
        return $this;
    }

    /**
     * Return filtered array of ProfServiceFees.
     *
     * @return array<ProfServiceFee>
     */
    public function getFilteredFees(string $feeTypeId, string $fixedOrAssessed): array
    {
        $fees = match ($feeTypeId) {
            ProfServiceFee::TYPE_CURRENT_FEE => $this->getProfServiceFeesByType(ProfServiceFee::TYPE_CURRENT_FEE),
            ProfServiceFee::TYPE_ESTIMATED_FEE => $this->getProfServiceFeesByType(ProfServiceFee::TYPE_ESTIMATED_FEE),
            ProfServiceFee::TYPE_PREVIOUS_FEE => $this->getProfServiceFeesByType(ProfServiceFee::TYPE_PREVIOUS_FEE),
            default => throw new \DomainException('Invalid Fee type Id:' . $feeTypeId),
        };

        return array_filter($fees, function ($profServiceFee) use ($fixedOrAssessed): bool {
            /* @var $profServiceFee ProfServiceFee */
            return $profServiceFee->getAssessedOrFixed() === $fixedOrAssessed;
        });
    }

    /**
     * @param string $feeTypeId "current"|"estimated"|"previous"
     *
     * @return array<ProfServiceFee>
     */
    public function getProfServiceFeesByType(string $feeTypeId): array
    {
        if (
            !in_array(
                $feeTypeId,
                [
                    ProfServiceFee::TYPE_CURRENT_FEE,
                    ProfServiceFee::TYPE_PREVIOUS_FEE,
                    ProfServiceFee::TYPE_ESTIMATED_FEE,
                ]
            )
        ) {
            throw new \DomainException('Invalid feeTypeId: ' . $feeTypeId);
        }

        return array_filter($this->getProfServiceFees(), function (ProfServiceFee $profServiceFee) use ($feeTypeId): bool {
            return $profServiceFee->getFeeTypeId() === $feeTypeId;
        });
    }

    /**
     * Returns current Fixed service fees.
     *
     * @return array<ProfServiceFee>
     */
    public function getCurrentFixedServiceFees(): array
    {
        return $this->getFilteredFees(
            ProfServiceFee::TYPE_CURRENT_FEE,
            ProfServiceFee::TYPE_FIXED_FEE
        );
    }

    /**
     * Returns current Assessed service fees.
     *
     * @return array<ProfServiceFee>
     */
    public function getCurrentAssessedServiceFees(): array
    {
        return $this->getFilteredFees(
            ProfServiceFee::TYPE_CURRENT_FEE,
            ProfServiceFee::TYPE_ASSESSED_FEE
        );
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

    /**
     * @return array<ProfServiceFee>
     */
    public function getProfServiceFees(): array
    {
        return $this->profServiceFees;
    }

    /**
     * @param array<ProfServiceFee> $profServiceFees
     */
    public function setProfServiceFees(array $profServiceFees): static
    {
        $this->profServiceFees = $profServiceFees;

        return $this;
    }

    /**
     * @return array<ProfServiceFee>
     */
    public function getCurrentProfServiceFees(): array
    {
        return array_filter($this->getProfServiceFees(), function ($profServiceFee) {
            return $profServiceFee->isCurrentFee();
        });
    }

    /**
     * Has Report got profServiceFee?
     */
    public function hasProfServiceFeeWithId(int $id): bool
    {
        return array_any($this->getProfServiceFees(), fn ($profServiceFee) => $profServiceFee->getId() == $id);

    }

    public function getFeeTotals(): array
    {
        $fixedServiceFees = $this->getFilteredFees(
            ProfServiceFee::TYPE_CURRENT_FEE,
            ProfServiceFee::TYPE_FIXED_FEE
        );
        $assessedServiceFees = $this->getFilteredFees(
            ProfServiceFee::TYPE_CURRENT_FEE,
            ProfServiceFee::TYPE_ASSESSED_FEE
        );

        $feeTotals = [];
        $feeTotals['totalFixedFeesReceived'] = $this->getTotalReceivedFees($fixedServiceFees);
        $feeTotals['totalFixedFeesCharged'] = $this->getTotalChargedFees($fixedServiceFees);
        $feeTotals['totalAssessedFeesReceived'] = $this->getTotalReceivedFees($assessedServiceFees);
        $feeTotals['totalAssessedFeesCharged'] = $this->getTotalChargedFees($assessedServiceFees);

        return $feeTotals;
    }

    /**
     * Calculate total Received Fees.
     */
    private function getTotalReceivedFees(array $profFees): float
    {
        $total = 0.00;

        foreach ($profFees as $profFee) {
            $total += $profFee->getAmountReceived();
        }

        return $total;
    }

    /**
     * Calculate total Charged Fees.
     *
     * @param array<ProfServiceFee> $profFees
     */
    private function getTotalChargedFees(array $profFees): float
    {
        $total = 0.00;
        foreach ($profFees as $profFee) {
            $total += $profFee->getAmountCharged();
        }
        return $total;
    }
}
