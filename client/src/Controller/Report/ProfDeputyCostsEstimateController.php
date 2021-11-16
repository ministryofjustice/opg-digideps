<?php

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Entity\Report\Report;
use App\Form as FormDir;
use App\Resolver\SubSectionRoute\ProfCostsEstimateSubSectionRouteResolver;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Base route.
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
        'prof-deputy-estimate-management-costs',
    ];

    public function __construct(private RestClient $restClient, private ReportApi $reportApi)
    {
    }

    /**
     * @Route("", name="prof_deputy_costs_estimate")
     * @Template("@App/Report/ProfDeputyCostsEstimate/start.html.twig")
     *
     * @param $reportId
     */
    public function startAction($reportId, ProfCostsEstimateSubSectionRouteResolver $routeResolver): array|\Symfony\Component\HttpFoundation\RedirectResponse
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
     * @Template("@App/Report/ProfDeputyCostsEstimate/howCharged.html.twig")
     *
     * @param $reportId
     */
    public function howChargedAction(Request $request, $reportId): array|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        $from = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, ['prof-deputy-costs-estimate-how-charged']);
        $currentHowChargedValue = $report->getProfDeputyCostsEstimateHowCharged();

        $form = $this->createForm(FormDir\Report\ProfDeputyCostsEstimateHowType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->persistUpdate($reportId, $form->getData(), ['deputyCostsEstimateHowCharged']);

            if ('summary' === $from) {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
            }

            return $this->redirectToRoute(
                $this->determineNextRouteFromHowCharged($request, $form, $currentHowChargedValue),
                ['reportId' => $reportId]
            );
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
            'backLink' => $this->generateUrl('prof_deputy_costs_estimate', ['reportId' => $reportId]),
        ];
    }

    /**
     * @Route("/breakdown", name="prof_deputy_costs_estimate_breakdown")
     * @Template("@App/Report/ProfDeputyCostsEstimate/breakdown.html.twig")
     *
     * @param $reportId
     */
    public function breakdownAction(Request $request, $reportId): array|\Symfony\Component\HttpFoundation\RedirectResponse
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

            if ('summary' === $from) {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
                $nextRoute = 'prof_deputy_costs_estimate_summary';
            } else {
                $nextRoute = 'prof_deputy_costs_estimate_more_info';
            }

            return $this->redirect($this->generateUrl($nextRoute, ['reportId' => $reportId]));
        }

        return [
            'backLink' => $this->generateUrl('summary' === $from ? 'prof_deputy_costs_estimate_summary' : 'prof_deputy_costs_estimate_how_charged', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/more-info", name="prof_deputy_costs_estimate_more_info")
     * @Template("@App/Report/ProfDeputyCostsEstimate/moreInfo.html.twig")
     *
     * @param $reportId
     */
    public function moreInfoAction(Request $request, $reportId): array|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        $from = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, ['prof-deputy-costs-estimate-more-info']);

        $form = $this->createForm(FormDir\Report\ProfDeputyCostsEstimateMoreInfoType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->persistUpdate($reportId, $form->getData(), ['deputyCostsEstimateMoreInfo']);

            if ('summary' === $from) {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
            }

            return $this->redirect($this->generateUrl('prof_deputy_costs_estimate_summary', ['reportId' => $reportId]));
        }

        return [
            'backLink' => $this->generateUrl('summary' === $from ? 'prof_deputy_costs_estimate_summary' : 'prof_deputy_costs_estimate_breakdown', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/summary", name="prof_deputy_costs_estimate_summary")
     * @Template("@App/Report/ProfDeputyCostsEstimate/summary.html.twig")
     *
     * @param $reportId
     */
    public function summaryAction($reportId): array|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (EntityDir\Report\Status::STATE_NOT_STARTED == $report->getStatus()->getProfDeputyCostsEstimateState()['state']) {
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
     */
    private function persistUpdate($id, Report $report, array $groups)
    {
        $this->restClient->put('report/'.$id, $report, $groups);
    }

    /**
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

        return ('summary' === $request->get('from') || Report::PROF_DEPUTY_COSTS_TYPE_FIXED === $updatedHowCharged) ?
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
        return Report::PROF_DEPUTY_COSTS_TYPE_FIXED === $originalHowChargedValue &&
            Report::PROF_DEPUTY_COSTS_TYPE_FIXED !== $updatedHowCharged;
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'profDeputyCostsEstimate';
    }
}
