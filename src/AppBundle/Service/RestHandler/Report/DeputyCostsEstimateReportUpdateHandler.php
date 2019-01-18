<?php

namespace AppBundle\Service\RestHandler\Report;

use AppBundle\Entity\Report\ProfDeputyEstimateCost;
use AppBundle\Entity\Report\Report;
use Doctrine\ORM\EntityManager;

class DeputyCostsEstimateReportUpdateHandler implements ReportUpdateHandlerInterface
{

    /**
     * DeputyCostsEstimateReportUpdateHandler constructor.
     *
     * @param EntityManager $em
     * @param CasrecVerificationService $casrecVerificationService
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
        $this->updateHowCharged($report, $data);
        $this->updateBreakdownEstimates($report, $data);

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
     */
    private function updateBreakdownEstimates(Report $report, array $data)
    {
        if (array_key_exists('prof_deputy_estimate_costs', $data)) {
            $defaultCostTypeIds = array_column($report->getProfDeputyEstimateCostTypeIds(), 'typeId');

            foreach ($data['prof_deputy_estimate_costs'] as $postedProfDeputyEstimateCostType) {
                if (in_array(
                    $postedProfDeputyEstimateCostType['prof_deputy_estimate_cost_type_id'],
                    $defaultCostTypeIds
                )) {
                    $profDeputyEstimateCost = $report->getProfDeputyEstimateCostByTypeId(
                        $postedProfDeputyEstimateCostType['prof_deputy_estimate_cost_type_id']
                    );

                    // update if exists, or instantiate a new entitys
                    if ($profDeputyEstimateCost instanceof ProfDeputyEstimateCost) {
                        $profDeputyEstimateCost->setAmount($postedProfDeputyEstimateCostType['amount']);
                    } else {
                        $profDeputyEstimateCost = new ProfDeputyEstimateCost(
                            $report,
                            $postedProfDeputyEstimateCostType['prof_deputy_estimate_cost_type_id'],
                            $postedProfDeputyEstimateCostType['has_more_details'],
                            $postedProfDeputyEstimateCostType['amount']
                        );
                    }
                    if ($profDeputyEstimateCost->getHasMoreDetails()) {
                        $profDeputyEstimateCost->setMoreDetails($postedProfDeputyEstimateCostType['more_details']);
                    }

                    $this->em->persist($profDeputyEstimateCost);
                }
            }
            $report->updateSectionsStatusCache([Report::SECTION_PROF_DEPUTY_COSTS_ESTIMATE]);

            $this->em->flush();
        }

        return $this;
    }
}
