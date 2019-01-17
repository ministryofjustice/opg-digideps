<?php

namespace AppBundle\Service\RestHandler\Report;

use AppBundle\Entity\Report\Report;

class DeputyCostsEstimateReportUpdateHandler implements ReportUpdateHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(Report $report, array $data)
    {
        if (array_key_exists('prof_deputy_costs_estimate_how_charged', $data)) {
            $report->setProfDeputyCostsEstimateHowCharged($data['prof_deputy_costs_estimate_how_charged']);
            $report->updateSectionsStatusCache([Report::SECTION_PROF_DEPUTY_COSTS_ESTIMATE]);
        }

        // todo add more updates relevant to the Deputy Costs Estimate section.
    }
}
