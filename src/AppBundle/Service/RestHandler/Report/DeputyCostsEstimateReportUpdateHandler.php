<?php

namespace AppBundle\Service\RestHandler\Report;

use AppBundle\Entity\Report\ProfDeputyEstimateCost;
use AppBundle\Entity\Report\Report;
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
            ->updateBreakdownEstimates($report, $data);

    }

    /**
     * @param Report $report
     * @param array $data
     * @return $this
     */
    private function updateHowCharged(Report $report, array $data)
    {
        if (array_key_exists('prof_deputy_costs_estimate_how_charged', $data)) {
            $report->setProfDeputyCostsEstimateHowCharged($data['prof_deputy_costs_estimate_how_charged']);
            $report->updateSectionsStatusCache([Report::SECTION_PROF_DEPUTY_COSTS_ESTIMATE]);
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

        $report->updateSectionsStatusCache([Report::SECTION_PROF_DEPUTY_COSTS_ESTIMATE]);

        $this->em->flush();

        return $this;
    }

    /**
     * @param array $data
     * @return bool
     */
    private function verifyProfDeputyEstimateCostData(array $data)
    {
        if (!isset($data['prof_deputy_estimate_cost_type_id']) || !isset($data['amount']) || !isset($data['has_more_details'])) {
            return false;
        }

        if (isset($data['has_more_details']) && true === $data['has_more_details'] && !isset($data['more_details'])) {
            return false;
        }

        return true;
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
}
