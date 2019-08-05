<?php

namespace AppBundle\Service\RestHandler\Report;

use AppBundle\Entity\Report\ProfDeputyEstimateCost;
use AppBundle\Entity\Report\Report;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

class DeputyCostsEstimateReportUpdateHandler implements ReportUpdateHandlerInterface
{

    /** @var EntityManager */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Report $report, array $data)
    {
        $this
            ->updateHowCharged($report, $data)
            ->updateBreakdownEstimates($report, $data)
            ->updateMoreInfo($report, $data)
            ->updateManagementCost($report, $data)
        ;

        $report->updateSectionsStatusCache([Report::SECTION_PROF_DEPUTY_COSTS_ESTIMATE]);
    }

    /**
     * @param Report $report
     * @param array $data
     * @return $this
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function updateHowCharged(Report $report, array $data)
    {
        if (array_key_exists('prof_deputy_costs_estimate_how_charged', $data)) {
            $report->setProfDeputyCostsEstimateHowCharged($data['prof_deputy_costs_estimate_how_charged']);

            if (Report::PROF_DEPUTY_COSTS_TYPE_FIXED !== $report->getProfDeputyCostsEstimateHowCharged()) {
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
     * @param Report $report
     * @param array $data
     * @return $this
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function updateBreakdownEstimates(Report $report, array $data)
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

    /**
     * @param array $data
     * @return bool
     */
    private function verifyProfDeputyEstimateCostData(array $data)
    {
        return array_key_exists('prof_deputy_estimate_cost_type_id', $data) &&
            array_key_exists('amount', $data) &&
            array_key_exists('has_more_details', $data) &&
            array_key_exists('more_details', $data);
    }

    /**
     * @param Report $report
     * @param array $data
     * @return void
     */
    private function attachProfDeputyEstimateCostsToReport(Report $report, array $data)
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

    /**
     * @param array $data
     * @param ProfDeputyEstimateCost $profDeputyEstimateCost
     */
    private function updateExistingProfDeputyEstimateCost(array $data, ProfDeputyEstimateCost $profDeputyEstimateCost)
    {
        $profDeputyEstimateCost->setAmount($data['amount']);
    }

    /**
     * @param Report $report
     * @param $postedProfDeputyEstimateCostType
     * @return ProfDeputyEstimateCost
     */
    private function createProfDeputyEstimateCost(Report $report, $postedProfDeputyEstimateCostType)
    {
        return (new ProfDeputyEstimateCost())
            ->setReport($report)
            ->setProfDeputyEstimateCostTypeId($postedProfDeputyEstimateCostType['prof_deputy_estimate_cost_type_id'])
            ->setHasMoreDetails($postedProfDeputyEstimateCostType['has_more_details'])
            ->setAmount($postedProfDeputyEstimateCostType['amount']);
    }

    /**
     * @param Report $report
     * @param array $data
     * @return $this
     */
    private function updateMoreInfo(Report $report, array $data)
    {
        if (array_key_exists('prof_deputy_costs_estimate_has_more_info', $data)) {
            $report->setProfDeputyCostsEstimateHasMoreInfo($data['prof_deputy_costs_estimate_has_more_info']);
        }

        if (array_key_exists('prof_deputy_costs_estimate_more_info_details', $data)) {
            $report->setProfDeputyCostsEstimateMoreInfoDetails($data['prof_deputy_costs_estimate_more_info_details']);
        }

        if ('no' === $report->getProfDeputyCostsEstimateHasMoreInfo()) {
            $report->setProfDeputyCostsEstimateMoreInfoDetails(null);
        }

        return $this;
    }

    /**
     * @param Report $report
     * @param array $data
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function updateManagementCost(Report $report, array $data) {
        if (array_key_exists('prof_deputy_management_cost_amount', $data)) {
            $report->setProfDeputyCostsEstimateManagementCostAmount($data['prof_deputy_management_cost_amount']);
            $this->em->flush();
        }
    }
}
