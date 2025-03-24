<?php

namespace App\Service\RestHandler\Report;

use App\Entity\Report\ProfDeputyInterimCost;
use App\Entity\Report\Report;
use Doctrine\ORM\EntityManagerInterface;

class DeputyCostsReportUpdateHandler implements ReportUpdateHandlerInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function handle(Report $report, array $data)
    {
        $this
            ->updateHowCharged($report, $data)
            ->updateInterimCosts($report, $data)
            ->updateHasPreviousCosts($report, $data)
            ->updateFixedCostAmount($report, $data)
            ->updateAmountToScco($report, $data)
            ->updateReasonBeyondEstimate($report, $data);

        $report->updateSectionsStatusCache([Report::SECTION_PROF_DEPUTY_COSTS]);
    }

    /**
     * @return $this
     */
    private function updateHowCharged(Report $report, array $data)
    {
        if (array_key_exists('prof_deputy_costs_how_charged', $data)) {
            $report->setProfDeputyCostsHowCharged($data['prof_deputy_costs_how_charged']);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function updateInterimCosts(Report $report, array $data)
    {
        if ($report->hasProfDeputyCostsHowChargedFixedOnly()) {
            $report->setProfDeputyCostsHasInterim(null);
            foreach ($report->getProfDeputyInterimCosts() as $ic) {
                $this->em->remove($ic);
            }
        } elseif ('yes' === $report->getProfDeputyCostsHasInterim()) {
            $report->setProfDeputyFixedCost(null);
        }

        if (!empty($data['prof_deputy_costs_has_interim']) && $data['prof_deputy_costs_has_interim']) {
            $report->setProfDeputyCostsHasInterim($data['prof_deputy_costs_has_interim']);
            // remove interim if changed to "no"
            if ('no' === $data['prof_deputy_costs_has_interim']) {
                foreach ($report->getProfDeputyInterimCosts() as $ic) {
                    $this->em->remove($ic);
                }
            } elseif ('yes' === $data['prof_deputy_costs_has_interim']) {
                $report->setProfDeputyFixedCost(null);
            }
        }

        if (array_key_exists('prof_deputy_interim_costs', $data)) {
            // wipe existing interim costs in order to overwrite
            // TODO consider keeping and updating the existing ones if simpler to implement
            foreach ($report->getProfDeputyInterimCosts() as $ic) {
                $this->em->remove($ic);
            }
            // add new
            foreach ($data['prof_deputy_interim_costs'] as $row) {
                if ($row['date'] && $row['amount']) {
                    $report->addProfDeputyInterimCosts(
                        new ProfDeputyInterimCost($report, new \DateTime($row['date']), $row['amount'])
                    );
                }
                if (count($report->getProfDeputyInterimCosts())) {
                    $report->setProfDeputyCostsHasInterim('yes');
                }
            }
            $this->em->flush();
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function updateHasPreviousCosts(Report $report, array $data)
    {
        if (!empty($data['prof_deputy_costs_has_previous']) && $data['prof_deputy_costs_has_previous']) {
            $report->setProfDeputyCostsHasPrevious($data['prof_deputy_costs_has_previous']);
            foreach ($report->getProfDeputyPreviousCosts() as $pc) {
                $this->em->remove($pc);
            }
            $report->updateSectionsStatusCache([Report::SECTION_PROF_DEPUTY_COSTS]);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function updateFixedCostAmount(Report $report, array $data)
    {
        if (array_key_exists('prof_deputy_fixed_cost', $data)) {
            $report->setProfDeputyFixedCost($data['prof_deputy_fixed_cost']);
            $report->updateSectionsStatusCache([Report::SECTION_PROF_DEPUTY_COSTS]);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function updateAmountToScco(Report $report, array $data)
    {
        if (array_key_exists('prof_deputy_costs_amount_to_scco', $data)) {
            $report->setProfDeputyCostsAmountToScco($data['prof_deputy_costs_amount_to_scco']);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function updateReasonBeyondEstimate(Report $report, array $data)
    {
        if (array_key_exists('prof_deputy_costs_reason_beyond_estimate', $data)) {
            $report->setProfDeputyCostsReasonBeyondEstimate($data['prof_deputy_costs_reason_beyond_estimate']);
            $report->updateSectionsStatusCache([Report::SECTION_PROF_DEPUTY_COSTS]);
        }

        return $this;
    }
}
