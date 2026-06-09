<?php

namespace OPG\Digideps\Backend\Service\RestHandler\Report;

use OPG\Digideps\Backend\Entity\Report\ProfDeputyEstimateCost;
use OPG\Digideps\Backend\Entity\Report\Report;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;

class DeputyCostsEstimateReportUpdateHandler implements ReportUpdateHandlerInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function handle(Report $report, array $data): void
    {
        $this
            ->updateHowCharged($report, $data)
            ->updateBreakdownEstimates($report, $data)
            ->updateMoreInfo($report, $data)
            ->updateManagementCost($report, $data)
        ;

        $report->updateSectionsStatusCache([Report::SECTION_PROF_DEPUTY_COSTS_ESTIMATE]);
    }

    private function updateHowCharged(Report $report, array $data): static
    {
        if (array_key_exists('prof_deputy_costs_estimate_how_charged', $data)) {
            $report->setProfDeputyCostsEstimateHowCharged($data['prof_deputy_costs_estimate_how_charged']);

            if ($report->getProfDeputyCostsEstimateHowCharged() !== Report::PROF_DEPUTY_COSTS_TYPE_FIXED) {
                return $this;
            }

            $report
                ->setProfDeputyCostsEstimateHasMoreInfo(null)
                ->setProfDeputyCostsEstimateMoreInfoDetails(null)
                ->setProfDeputyCostsEstimateManagementCostAmount(null);

            if (!$report->getProfDeputyEstimateCosts()->isEmpty()) {
                foreach ($report->getProfDeputyEstimateCosts() as $profDeputyEstimateCost) {
                    $report->getProfDeputyEstimateCosts()->removeElement($profDeputyEstimateCost);
                    $this->em->remove($profDeputyEstimateCost);
                }

                $this->em->flush();
            }
        }

        return $this;
    }

    /**
     * @return $this
     *
     * @throws OptimisticLockException
     */
    private function updateBreakdownEstimates(Report $report, array $data): static
    {
        if (!array_key_exists('prof_deputy_estimate_costs', $data)) {
            return $this;
        }

        $defaultCostTypeIds = array_column($report->getProfDeputyEstimateCostTypeIds(), 'typeId');

        foreach ($data['prof_deputy_estimate_costs'] as $data) {
            if (!$this->verifyProfDeputyEstimateCostData($data)) {
                throw new \InvalidArgumentException('Missing required data for updating breakdown estimates');
            }

            if (!in_array($data['prof_deputy_estimate_cost_type_id'], $defaultCostTypeIds)) {
                continue;
            }

            $this->attachProfDeputyEstimateCostsToReport($report, $data);
        }

        $this->em->flush();

        return $this;
    }

    private function verifyProfDeputyEstimateCostData(array $data): bool
    {
        return array_key_exists('prof_deputy_estimate_cost_type_id', $data)
            && array_key_exists('amount', $data)
            && array_key_exists('has_more_details', $data)
            && array_key_exists('more_details', $data);
    }

    private function attachProfDeputyEstimateCostsToReport(Report $report, array $data): void
    {
        $profDeputyEstimateCost = $report->getProfDeputyEstimateCostByTypeId($data['prof_deputy_estimate_cost_type_id']);

        if ($profDeputyEstimateCost instanceof ProfDeputyEstimateCost) {
            $this->updateExistingProfDeputyEstimateCost($data, $profDeputyEstimateCost);
        } else {
            $profDeputyEstimateCost = $this->createProfDeputyEstimateCost($report, $data);
            $report->addProfDeputyEstimateCost($profDeputyEstimateCost);
        }

        if ($profDeputyEstimateCost->getHasMoreDetails()) {
            $profDeputyEstimateCost->setMoreDetails($data['more_details']);
        }

        $this->em->persist($profDeputyEstimateCost);
    }

    private function updateExistingProfDeputyEstimateCost(array $data, ProfDeputyEstimateCost $profDeputyEstimateCost): void
    {
        $profDeputyEstimateCost->setAmount($data['amount']);
    }

    private function createProfDeputyEstimateCost(Report $report, $postedProfDeputyEstimateCostType): ProfDeputyEstimateCost
    {
        return new ProfDeputyEstimateCost()
            ->setReport($report)
            ->setProfDeputyEstimateCostTypeId($postedProfDeputyEstimateCostType['prof_deputy_estimate_cost_type_id'])
            ->setHasMoreDetails($postedProfDeputyEstimateCostType['has_more_details'])
            ->setAmount($postedProfDeputyEstimateCostType['amount']);
    }

    private function updateMoreInfo(Report $report, array $data): static
    {
        if (array_key_exists('prof_deputy_costs_estimate_has_more_info', $data)) {
            $report->setProfDeputyCostsEstimateHasMoreInfo($data['prof_deputy_costs_estimate_has_more_info']);
        }

        if (array_key_exists('prof_deputy_costs_estimate_more_info_details', $data)) {
            $report->setProfDeputyCostsEstimateMoreInfoDetails($data['prof_deputy_costs_estimate_more_info_details']);
        }

        if ($report->getProfDeputyCostsEstimateHasMoreInfo() === 'no') {
            $report->setProfDeputyCostsEstimateMoreInfoDetails(null);
        }

        return $this;
    }

    private function updateManagementCost(Report $report, array $data): void
    {
        if (array_key_exists('prof_deputy_management_cost_amount', $data) && is_numeric($data['prof_deputy_management_cost_amount'])) {
            $report->setProfDeputyCostsEstimateManagementCostAmount((float)$data['prof_deputy_management_cost_amount']);
            $this->em->flush();
        }
    }
}
