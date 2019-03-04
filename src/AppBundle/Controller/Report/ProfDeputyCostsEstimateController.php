<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report\Report;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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
        'prof-deputy-costs-estimate-how-charged',
        'prof-deputy-management-costs',
        'prof-deputy-estimate-costs',
        'prof-deputy-costs-estimate-more-info'
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
        $state = $report->getStatus()->getProfDeputyCostsEstimateState()['state'];

        $routeResolver = $this->get('resolver.prof_costs_estimate_subsection_route_resolver');
        if (null !== ($forwardRoute = $routeResolver->resolve($report, $state))) {
            return $this->redirectToRoute($forwardRoute, ['reportId' => $reportId]);
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
        $from = $request->get('from');
        $report = $this->getReportIfNotSubmitted($reportId, ['prof-deputy-costs-estimate-how-charged']);
        $currentHowChargedValue = $report->getProfDeputyCostsEstimateHowCharged();

        $form = $this->createForm(FormDir\Report\ProfDeputyCostsEstimateHowType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->persistUpdate($reportId, $form->getData(), ['deputyCostsEstimateHowCharged']);

            if ($from === 'summary') {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
            }

            return $this->redirectToRoute(
                $this->determineNextRouteFromHowCharged($request, $form, $currentHowChargedValue),
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
     * @Route("/management-costs", name="prof_deputy_management_costs")
     * @Template()
     */
    public function managementCostsAction(Request $request, $reportId)
    {
        $from = $request->get('from');
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (empty($report->getProfDeputyManagementCosts())) {
            // if none set generate other costs manually
            $managementCosts = $this->generateDefaultManagementCosts($report);

            $report->setProfDeputyManagementCosts($managementCosts);
        }

        $form = $this->createForm(FormDir\Report\ProfDeputyManagementCostType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->persistUpdate($reportId, $form->getData(), ['deputyCostsEstimateManagementCosts']);

            if ($from === 'summary') {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
                $nextRoute = 'prof_deputy_costs_estimate_summary';
            } else {
                $nextRoute = 'prof_deputy_costs_estimate_breakdown';
            }

            return $this->redirect($this->generateUrl($nextRoute, ['reportId' => $reportId]));
        }

        return [
            'backLink' =>$this->generateUrl( $from === 'summary' ? 'prof_deputy_costs_estimate_summary' : 'prof_deputy_costs_estimate_how_charged', ['reportId'=>$reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/breakdown", name="prof_deputy_costs_estimate_breakdown")
     * @Template()
     */
    public function breakdownAction(Request $request, $reportId)
    {
        $from = $request->get('from');
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (empty($report->getProfDeputyEstimateCosts())) {
            // if none set generate other costs manually
            $estimateCosts = $this->generateDefaultEstimateCosts($report);

            $report->setProfDeputyEstimateCosts($estimateCosts);
        }

        $form = $this->createForm(FormDir\Report\ProfDeputyEstimateCostsType::class, $report, []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->persistUpdate($reportId, $form->getData(), ['prof-deputy-estimate-costs']);

            if ($from === 'summary') {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
                $nextRoute = 'prof_deputy_costs_estimate_summary';
            } else {
                $nextRoute = 'prof_deputy_costs_estimate_more_info';
            }

            return $this->redirect($this->generateUrl($nextRoute, ['reportId' => $reportId]));
        }

        return [
            'backLink' =>$this->generateUrl( $from === 'summary' ? 'prof_deputy_costs_estimate_summary' : 'prof_deputy_management_costs', ['reportId'=>$reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/more-info", name="prof_deputy_costs_estimate_more_info")
     * @Template()
     */
    public function moreInfoAction(Request $request, $reportId)
    {
        $from = $request->get('from');
        $report = $this->getReportIfNotSubmitted($reportId, ['prof-deputy-costs-estimate-more-info']);

        $form = $this->createForm(FormDir\Report\ProfDeputyCostsEstimateMoreInfoType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->persistUpdate($reportId, $form->getData(), ['deputyCostsEstimateMoreInfo']);

            if ($from === 'summary') {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
            }

            return $this->redirect($this->generateUrl('prof_deputy_costs_estimate_summary', ['reportId' => $reportId]));
        }

        return [
            'backLink' =>$this->generateUrl( $from === 'summary' ? 'prof_deputy_costs_estimate_summary' : 'prof_deputy_costs_estimate_breakdown', ['reportId'=>$reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/summary", name="prof_deputy_costs_estimate_summary")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function summaryAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($report->getStatus()->getProfDeputyCostsEstimateState()['state'] == EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirect($this->generateUrl('prof_deputy_costs_estimate', ['reportId' => $reportId]));
        }

        $costBreakdown = Report::PROF_DEPUTY_COSTS_ESTIMATE_TYPE_FIXED === $report->getProfDeputyCostsEstimateHowCharged() ?
            null : $report->generateActualSubmittedEstimateCosts();

        return [
            'submittedEstimateCosts' => $costBreakdown,
            'report' => $report,
        ];
    }

    /**
     * Retrieves the list of default estimate cost type IDs using virtual property from api
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

    private function generateDefaultManagementCosts(EntityDir\Report\Report $report)
    {
        $managementCosts = [];

        $defaultManagementCostTypeIds = $report->getProfDeputyManagementCostTypeIds();
        foreach ($defaultManagementCostTypeIds as $defaultManagementCostType) {
            $managementCosts[] = new EntityDir\Report\ProfDeputyManagementCost(
                $defaultManagementCostType['typeId'],
                null,
                $defaultManagementCostType['hasMoreDetails'],
                null
            );

        }
        return $managementCosts;
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
     * @param $originalHowChargedValue
     * @return string
     */
    private function determineNextRouteFromHowCharged(Request $request, FormInterface $form, $originalHowChargedValue)
    {
        $updatedHowCharged = $form->getData()->getProfDeputyCostsEstimateHowCharged();

        if ($this->answerHasChangedFromFixedToNonFixed($originalHowChargedValue, $updatedHowCharged)) {
            return 'prof_deputy_management_costs';
        }

        return ($request->get('from') === 'summary' || $updatedHowCharged === Report::PROF_DEPUTY_COSTS_ESTIMATE_TYPE_FIXED) ?
            'prof_deputy_costs_estimate_summary' :
            'prof_deputy_management_costs';
    }

    /**
     * @param $originalHowChargedValue
     * @param $updatedHowCharged
     * @return bool
     */
    private function answerHasChangedFromFixedToNonFixed($originalHowChargedValue, $updatedHowCharged)
    {
        return $originalHowChargedValue === Report::PROF_DEPUTY_COSTS_ESTIMATE_TYPE_FIXED &&
            $updatedHowCharged !== Report::PROF_DEPUTY_COSTS_ESTIMATE_TYPE_FIXED;
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'profDeputyCostsEstimate';
    }
}
