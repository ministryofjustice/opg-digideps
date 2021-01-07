<?php

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Entity\Report\Report;
use App\Form as FormDir;
use App\Resolver\SubSectionRoute\ProfCostsEstimateSubSectionRouteResolver;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
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
        'prof-deputy-estimate-costs',
        'prof-deputy-costs-estimate-more-info',
        'prof-deputy-estimate-management-costs'
    ];

    /** @var RestClient */
    private $restClient;

    /** @var ReportApi */
    private $reportApi;

    public function __construct(
        RestClient $restClient,
        ReportApi $reportApi
    ) {
        $this->restClient = $restClient;
        $this->reportApi = $reportApi;
    }

    /**
     * @Route("", name="prof_deputy_costs_estimate")
     * @Template("App:Report/ProfDeputyCostsEstimate:start.html.twig")
     *
     * @param $reportId
     * @param ProfCostsEstimateSubSectionRouteResolver $routeResolver
     *
     * @return array|RedirectResponse
     */
    public function startAction($reportId, ProfCostsEstimateSubSectionRouteResolver $routeResolver)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $state = $report->getStatus()->getProfDeputyCostsEstimateState()['state'];

        if (null !== ($forwardRoute = $routeResolver->resolve($report, $state))) {
            return $this->redirectToRoute($forwardRoute, ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/how-charged", name="prof_deputy_costs_estimate_how_charged")
     * @Template("App:Report/ProfDeputyCostsEstimate:howCharged.html.twig")
     *
     * @param Request $request
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function howChargedAction(Request $request, $reportId)
    {
        $from = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, ['prof-deputy-costs-estimate-how-charged']);
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
     * @Route("/breakdown", name="prof_deputy_costs_estimate_breakdown")
     * @Template("App:Report/ProfDeputyCostsEstimate:breakdown.html.twig")
     *
     * @param Request $request
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function breakdownAction(Request $request, $reportId)
    {
        $from = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (empty($report->getProfDeputyEstimateCosts())) {
            // if none set generate other costs manually
            $estimateCosts = $this->generateDefaultEstimateCosts($report);

            $report->setProfDeputyEstimateCosts($estimateCosts);
        }

        $form = $this->createForm(FormDir\Report\ProfDeputyEstimateCostsType::class, $report, []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->persistUpdate($reportId, $form->getData(), ['prof-deputy-estimate-costs', 'prof-deputy-estimate-management-costs']);

            if ($from === 'summary') {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
                $nextRoute = 'prof_deputy_costs_estimate_summary';
            } else {
                $nextRoute = 'prof_deputy_costs_estimate_more_info';
            }

            return $this->redirect($this->generateUrl($nextRoute, ['reportId' => $reportId]));
        }

        return [
            'backLink' =>$this->generateUrl($from === 'summary' ? 'prof_deputy_costs_estimate_summary' : 'prof_deputy_costs_estimate_how_charged', ['reportId'=>$reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/more-info", name="prof_deputy_costs_estimate_more_info")
     * @Template("App:Report/ProfDeputyCostsEstimate:moreInfo.html.twig")
     * @param Request $request
     * @param $reportId
     * @return array|RedirectResponse
     */
    public function moreInfoAction(Request $request, $reportId)
    {
        $from = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, ['prof-deputy-costs-estimate-more-info']);

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
            'backLink' =>$this->generateUrl($from === 'summary' ? 'prof_deputy_costs_estimate_summary' : 'prof_deputy_costs_estimate_breakdown', ['reportId'=>$reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/summary", name="prof_deputy_costs_estimate_summary")
     * @Template("App:Report/ProfDeputyCostsEstimate:summary.html.twig")
     *
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function summaryAction($reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($report->getStatus()->getProfDeputyCostsEstimateState()['state'] == EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirect($this->generateUrl('prof_deputy_costs_estimate', ['reportId' => $reportId]));
        }

        $costBreakdown = Report::PROF_DEPUTY_COSTS_TYPE_FIXED === $report->getProfDeputyCostsEstimateHowCharged() ?
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
     *
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
     *
     * @param array $groups
     */
    private function persistUpdate($id, Report $report, array $groups)
    {
        $this->restClient->put('report/' . $id, $report, $groups);
    }

    /**
     * @param Request $request
     * @param FormInterface $form
     * @param $originalHowChargedValue
     *
     * @return string
     */
    private function determineNextRouteFromHowCharged(Request $request, FormInterface $form, $originalHowChargedValue)
    {
        $updatedHowCharged = $form->getData()->getProfDeputyCostsEstimateHowCharged();

        if ($this->answerHasChangedFromFixedToNonFixed($originalHowChargedValue, $updatedHowCharged)) {
            return 'prof_deputy_costs_estimate_breakdown';
        }

        return ($request->get('from') === 'summary' || $updatedHowCharged === Report::PROF_DEPUTY_COSTS_TYPE_FIXED) ?
            'prof_deputy_costs_estimate_summary' :
            'prof_deputy_costs_estimate_breakdown';
    }

    /**
     * @param $originalHowChargedValue
     * @param $updatedHowCharged
     *
     * @return bool
     */
    private function answerHasChangedFromFixedToNonFixed($originalHowChargedValue, $updatedHowCharged)
    {
        return $originalHowChargedValue === Report::PROF_DEPUTY_COSTS_TYPE_FIXED &&
            $updatedHowCharged !== Report::PROF_DEPUTY_COSTS_TYPE_FIXED;
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'profDeputyCostsEstimate';
    }
}
