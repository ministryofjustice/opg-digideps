<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report\Report;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
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
        'prof-deputy-costs-estimate-how-charged'
    ];

    /**
     * @Route("", name="prof_deputy_costs_estimate")
     * @Template()
     *
     * @param $reportId
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
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
     *
     * @param Request $request
     * @param $reportId
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function howChargedAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, ['prof-deputy-costs-estimate-how-charged']);

        $form = $this->createForm(FormDir\Report\ProfDeputyCostsEstimateHowType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->persistUpdate($reportId, $form->getData(), ['deputyCostsEstimateHowCharged']);

            return $this->redirectToRoute(
                $this->determineNextRouteFromHowCharged($request, $form),
                ['reportId'=>$reportId]
            );
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
            'backLink' => $this->generateUrl('prof_deputy_costs_estimate', ['reportId'=>$reportId])
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
            $otherCosts = $this->generateDefaultEstimateCosts($report);

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
        $estimateCosts = [];

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
     * @param $id
     * @param Report $report
     * @param array $groups
     */
    private function persistUpdate($id, Report $report, array $groups)
    {
        $this->getRestClient()->put('report/' . $id, $report, $groups);
    }

    /**
     * @param Request $request
     * @param FormInterface $form
     * @return string
     */
    private function determineNextRouteFromHowCharged(Request $request, FormInterface $form)
    {
        $howCharged = $form->getData()->getProfDeputyCostsEstimateHowCharged();

        return ($request->get('from') === 'summary' || $howCharged === Report::PROF_DEPUTY_COSTS_ESTIMATE_TYPE_FIXED) ?
            'prof_deputy_costs_estimate_summary' :
            'prof_deputy_costs_estimate_breakdown';
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'profDeputyCostsEstimate';
    }
}
