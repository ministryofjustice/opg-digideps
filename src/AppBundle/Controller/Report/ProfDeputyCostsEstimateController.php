<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base route
 *
 * @Route("/report/{reportId}/prof-deputy-costs-estimate")
 */
class ProfDeputyCostsEstimateController extends AbstractController
{
    private static $jmsGroups = [
        'status',
        // TODO
    ];

    /**
     * @Route("", name="prof_deputy_costs_estimate")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function startAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getProfDeputyCostsEstimateState()['state'] != EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('prof_deputy_costs_estimate_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/how-charged", name="prof_deputy_costs_estimate_how_charged")
     * @Template()
     */
    public function howChargedAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $from = $request->get('from');

        $form = $this->createForm(FormDir\Report\ProfDeputyCostsEstimateHowType::class, $report);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();

            $this->getRestClient()->put('report/' . $reportId, $data, ['deputyCostsHowCharged']);

            if ($from === 'summary') {
                $nextRoute = 'prof_deputy_costs_summary';
            } else {
                $nextRoute = 'prof_deputy_costs_previous_received_exists';
            }

            return $this->redirectToRoute($nextRoute, ['reportId'=>$reportId]);
        }


        return [
            'report' => $report,
            'form' => $form->createView(),
            'backLink' => $this->generateUrl($from === 'summary' ? 'prof_deputy_costs_estimate_summary' : 'prof_deputy_costs_estimate', ['reportId'=>$reportId])
        ];
    }



    /**
     * @Route("/breakdown", name="prof_deputy_costs_estimate_breakdown")
     * @Template()
     */
    public function breakdown(Request $request, $reportId)
    {
        $from = $request->get('from');
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (empty($report->getProfDeputyEstimateCosts())) {
            // if none set generate other costs manually
            $otherCosts = $this->generateDefaultOtherCosts($report);

            $report->setProfDeputyOtherCosts($otherCosts);
        }

        $form = $this->createForm(FormDir\Report\ProfDeputyOtherCostsType::class, $report, []);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getRestClient()->put('report/' . $report->getId(), $form->getData(), ['prof-deputy-other-costs']);

            return $this->redirect($this->generateUrl('prof_deputy_costs_summary', ['reportId' => $reportId]));
        }

        return [
            'backLink' =>$this->generateUrl( $from === 'summary' ? 'prof_deputy_costs_summary' : 'prof_deputy_costs_amount_scco', ['reportId'=>$reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * Retrieves the list of default other cost type IDs using virtual property from api
     * Used to generate the page since with no initial data, we cant display form inputs
     * without this list.
     *
     * @param EntityDir\Report\Report $report
     * @return array
     */
    private function generateDefaultEstimateCosts(EntityDir\Report\Report $report)
    {
        $otherCosts = [];

        $defaultEstimateCostTypeIds = $report->getProfDeputyEstimateCostTypeIds();
        foreach ($defaultEstimateCostTypeIds as $defaultEstimateCostType) {
            $estimateCosts[] = new EntityDir\Report\ProfDeputyEstimateCost(
                $defaultEstimateCostType['typeId'],
                null,
                $defaultEstimateCostType['hasMoreDetails'],
                null
            );

        }
        return $estimateCosts;
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'profDeputyCostsEstimate ';
    }
}
