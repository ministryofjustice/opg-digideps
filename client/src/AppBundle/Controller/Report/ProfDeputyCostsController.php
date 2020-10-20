<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Resolver\SubSectionRoute\ProfCostsSubSectionRouteResolver;
use AppBundle\Service\Client\Internal\ReportApi;
use AppBundle\Service\Client\RestClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base route
 *
 * @Route("/report/{reportId}/prof-deputy-costs")
 */
class ProfDeputyCostsController extends AbstractController
{
    private static $jmsGroups = [
        'status',
        'prof-deputy-other-costs',
        'prof-deputy-costs-how-charged',
        'report-prof-deputy-costs',
        'report-prof-deputy-costs-prev', 'prof-deputy-costs-prev',
        'report-prof-deputy-costs-interim', 'prof-deputy-costs-interim',
        'report-prof-deputy-costs-scco',
        'report-prof-deputy-fixed-cost',
        'prof-deputy-other-costs'
    ];

    /** @var RestClient */
    private $restClient;

    /** @var ReportApi */
    private $reportApi;

    public function __construct(
        RestClient $restClient,
        ReportApi $reportApi
    )
    {
        $this->restClient = $restClient;
        $this->reportApi = $reportApi;
    }

    /**
     * @Route("", name="prof_deputy_costs")
     * @Template("AppBundle:Report/ProfDeputyCosts:start.html.twig")
     *
     * @param $reportId
     * @param ProfCostsSubSectionRouteResolver $routeResolver
     *
     * @return array|RedirectResponse
     */
    public function startAction($reportId, ProfCostsSubSectionRouteResolver $routeResolver)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $state = $report->getStatus()->getProfDeputyCostsState()['state'];

        if (null !== ($forwardRoute = $routeResolver->resolve($report, $state))) {
            return $this->redirectToRoute($forwardRoute, ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/how-charged", name="prof_deputy_costs_how_charged")
     * @Template("AppBundle:Report/ProfDeputyCosts:howCharged.html.twig")
     *
     * @param Request $request
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function howChargedAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $from = $request->get('from');

        $form = $this->createForm(FormDir\Report\ProfDeputyCostHowType::class, $report);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->restClient->put('report/' . $reportId, $data, ['deputyCostsHowCharged']);

            if ($from === 'summary') {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
                $nextRoute = 'prof_deputy_costs_summary';
            } else {
                $nextRoute = 'prof_deputy_costs_previous_received_exists';
            }

            return $this->redirectToRoute($nextRoute, ['reportId'=>$reportId]);
        }


        return [
            'report' => $report,
            'form' => $form->createView(),
            'backLink' => $this->generateUrl($from === 'summary' ? 'prof_deputy_costs_summary' : 'prof_deputy_costs', ['reportId'=>$reportId])
        ];
    }

    /**
     * @Route("/previous-received-exists", name="prof_deputy_costs_previous_received_exists")
     * @Template("AppBundle:Report/ProfDeputyCosts:previousReceivedExists.html.twig")
     * @param Request $request
     * @param $reportId
     * @return array|RedirectResponse
     */
    public function previousReceivedExists(Request $request, $reportId)
    {
        $from = $request->get('from', 'exist');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\YesNoType::class, $report, [
            'field' => 'profDeputyCostsHasPrevious',
            'translation_domain' => 'report-prof-deputy-costs'
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Report */
            switch ($data->getProfDeputyCostsHasPrevious()) {
                case 'yes':
                    // no need to save. "Yes" will be set when one entry is added to keep db data consistent
                    return $this->redirectToRoute('prof_deputy_costs_previous_received', ['reportId' => $reportId, 'from'=>$from]);
                case 'no':
                    // store and go to next route
                    $this->restClient->put('report/' . $reportId, $data, ['profDeputyCostsHasPrevious']);

                    if ($from =='summary') {
                        $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
                        $nextRoute = 'prof_deputy_costs_summary';
                    } else if ($report->hasProfDeputyCostsHowChargedFixedOnly()) {
                        $nextRoute = 'prof_deputy_costs_received';
                    } else {
                        $nextRoute = 'prof_deputy_costs_inline_interim_19b_exists';
                    }

                    return $this->redirectToRoute($nextRoute, ['reportId' => $reportId]);
            }
        }

        return [
            // previous step could be interim or fixed. easier NOT showing any backlink
            'backLink' => $this->generateUrl($from === 'summary' ? 'prof_deputy_costs_summary' : 'prof_deputy_costs_how_charged', ['reportId'=>$reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/previous-received/{previousReceivedId}", name="prof_deputy_costs_previous_received")
     * @Template("AppBundle:Report/ProfDeputyCosts:previousReceived.html.twig")
     *
     * @param Request $request
     * @param $reportId
     * @param null $previousReceivedId
     *
     * @return array|RedirectResponse
     */
    public function previousReceived(Request $request, $reportId, $previousReceivedId = null)
    {
        $from = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        // create (add mode) or load transaction (edit mode)
        if ($previousReceivedId) {
            $pr = $this->restClient->get('/prof-deputy-previous-cost/' . $previousReceivedId, 'Report\\ProfDeputyPreviousCost');
        } else {
            $pr = new EntityDir\Report\ProfDeputyPreviousCost();
        }

        $form = $this->createForm(FormDir\Report\ProfDeputyCostPreviousType::class, $pr, [
            'editMode' =>  !empty($previousReceivedId)
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($previousReceivedId) { // edit
                $this->restClient->put('/prof-deputy-previous-cost/' . $previousReceivedId, $pr, ['profDeputyPrevCosts']);
                $request->getSession()->getFlashBag()->add('notice', 'Cost edited');
            } else {
                $this->restClient->post('/report/' . $reportId . '/prof-deputy-previous-cost', $pr, ['profDeputyPrevCosts']);
                $request->getSession()->getFlashBag()->add('notice', 'Cost added');
            }

            if ($form->getClickedButton()->getName() === 'saveAndAddAnother') {
                $nextRoute = 'prof_deputy_costs_previous_received';
            } else if ($from === 'summary') {
                $nextRoute = 'prof_deputy_costs_summary';
            } else if ($report->hasProfDeputyCostsHowChargedFixedOnly()) {
                $nextRoute = 'prof_deputy_costs_received';
            } else {
                $nextRoute = 'prof_deputy_costs_inline_interim_19b_exists';
            }

            return $this->redirectToRoute($nextRoute, ['reportId' => $reportId]);
        }

        return [
            'backLink' => $this->generateUrl($from =='summary' ? 'prof_deputy_costs_summary' : 'prof_deputy_costs_previous_received_exists', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/previous-received/{previousReceivedId}/delete", name="prof_deputy_costs_previous_received_delete")
     * @Template("AppBundle:Common:confirmDelete.html.twig")
     *
     * @param Request $request
     * @param $reportId
     * @param int $previousReceivedId
     *
     * @return array|RedirectResponse
     */
    public function previousCostDelete(Request $request, $reportId, int $previousReceivedId)
    {
        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->delete('report/' . $report->getId() . '/prof-deputy-previous-cost/' . $previousReceivedId);

            $request->getSession()->getFlashBag()->add(
                'notice',
                'Cost deleted'
            );

            return $this->redirect($this->generateUrl('prof_deputy_costs_summary', ['reportId' => $reportId]));
        }

        $cost = $this->restClient->get('/prof-deputy-previous-cost/' . $previousReceivedId, 'Report\ProfDeputyPreviousCost');

        return [
            'translationDomain' => 'report-prof-deputy-costs',
            'report' => $report,
            'form' => $form->createView(),
            'summary' => [
                ['label' => 'deletePage.summary.startDate', 'value' => $cost->getStartDate(), 'format' => 'date'],
                ['label' => 'deletePage.summary.endDate', 'value' => $cost->getEndDate(), 'format' => 'date'],
                ['label' => 'deletePage.summary.amount', 'value' => $cost->getAmount(), 'format' => 'money'],
            ],
            'backLink' => $this->generateUrl('prof_deputy_costs_summary', ['reportId' => $reportId]),
        ];
    }


    /**
     * @Route("/interim-exists", name="prof_deputy_costs_inline_interim_19b_exists")
     * @Template("AppBundle:Report/ProfDeputyCosts:interimExists.html.twig")
     *
     * @param Request $request
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function interimExists(Request $request, $reportId)
    {
        $from = $request->get('from', 'exist');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\YesNoType::class, $report, [
                'field' => 'profDeputyCostsHasInterim',
                'translation_domain' => 'report-prof-deputy-costs'
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Report */

            // store yes or no
            $this->restClient->put('report/' . $reportId, $data, ['profDeputyCostsHasInterim']);

            // next route calculation
            switch ($data->getProfDeputyCostsHasInterim()) {
                case 'yes':
                    // go to interim page, and pass by the "from"
                    return $this->redirectToRoute('prof_deputy_costs_inline_interim_19b', ['reportId' => $reportId, 'from'=>$from]);
                case 'no':
                    if ($from === 'summary') {
                        $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
                        $nextRoute = 'prof_deputy_costs_summary';
                        // TODO consider going to fixed costs adding from=summmary if not set
                    } else {
                        $nextRoute = 'prof_deputy_costs_received';
                    }

                    return $this->redirectToRoute($nextRoute, ['reportId' => $reportId]);
            }
        }

        return [
            'backLink' => null,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/interim", name="prof_deputy_costs_inline_interim_19b")
     * @Template("AppBundle:Report/ProfDeputyCosts:interim.html.twig")
     *
     * @param Request $request
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function interim(Request $request, $reportId)
    {
        $from = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        // fill missing interim with empty entities, in order for 3 subforms in total to appear
        for($i = count($report->getProfDeputyInterimCosts()); $i < 3; $i++) {
            $report->addProfDeputyInterimCosts(new EntityDir\Report\ProfDeputyInterimCost());
        }

        $form = $this->createForm(FormDir\Report\ProfDeputyCostInterimType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->restClient->put('/report/' . $reportId, $report, ['profDeputyInterimCosts']);

            if ($from === 'summary') {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
                $nextRoute = 'prof_deputy_costs_summary';
            } else { // saveAndContinue
                $nextRoute = 'prof_deputy_costs_amount_scco';
            }

            return $this->redirectToRoute($nextRoute, ['reportId' => $reportId]);
        }

        return [
            'backLink' => $this->generateUrl($from =='summary' ? 'prof_deputy_costs_summary' : 'prof_deputy_costs_inline_interim_19b_exists', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/costs-received", name="prof_deputy_costs_received")
     * @Template("AppBundle:Report/ProfDeputyCosts:fixedCost.html.twig")
     *
     * @param Request $request
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function fixedCostAction(Request $request, $reportId)
    {
        $from = $request->get('from');
        /** @var EntityDir\Report\Report $report */
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(FormDir\Report\ProfDeputyFixedCostType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->restClient->put('/report/' . $reportId, $report, ['profDeputyFixedCost']);

            if ($from === 'summary') {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
                $nextRoute = 'prof_deputy_costs_summary';
            } else {
                $nextRoute = 'prof_deputy_costs_amount_scco';
                if ($report->hasProfDeputyCostsHowChargedFixedOnly()) {
                    $nextRoute = 'prof_deputy_costs_breakdown';
                }
            }

            return $this->redirectToRoute($nextRoute, ['reportId' => $reportId]);
        }

        return [
            'backLink' => $from =='summary' ? $this->generateUrl('prof_deputy_costs_summary', ['reportId' => $reportId]) : null,
            'form' => $form->createView(),
            'report' => $report
        ];

    }

    /**
     * @Route("/amount-scco", name="prof_deputy_costs_amount_scco")
     * @Template("AppBundle:Report/ProfDeputyCosts:amountToScco.html.twig")
     *
     * @param Request $request
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function amountToSccoAction(Request $request, $reportId)
    {
        $from = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(FormDir\Report\ProfDeputyCostSccoType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->restClient->put('/report/' . $reportId, $report, ['profDeputyCostsScco']);

            if ($from === 'summary') {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
                $nextRoute = 'prof_deputy_costs_summary';
            } else {
                $nextRoute = 'prof_deputy_costs_breakdown';
            }

            return $this->redirectToRoute($nextRoute, ['reportId' => $reportId]);
        }

        return [
            // backlink depends on "fixed" being selected. Simpler not to show a backlink unless necessary
            'backLink' => $from =='summary' ? $this->generateUrl('prof_deputy_costs_summary', ['reportId' => $reportId]) : null,
            'form' => $form->createView(),
            'report' => $report
        ];
    }

    /**
     * @Route("/breakdown", name="prof_deputy_costs_breakdown")
     * @Template("AppBundle:Report/ProfDeputyCosts:breakdown.html.twig")
     *
     * @param Request $request
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function breakdown(Request $request, $reportId)
    {
        $from = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (empty($report->getProfDeputyOtherCosts())) {
            // if none set generate other costs manually
            $otherCosts = $this->generateDefaultOtherCosts($report);

            $report->setProfDeputyOtherCosts($otherCosts);
        }

        $form = $this->createForm(FormDir\Report\ProfDeputyOtherCostsType::class, $report, []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->put('report/' . $report->getId(), $form->getData(), ['prof-deputy-other-costs']);

            if ($from === 'summary') {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
            }

            return $this->redirect($this->generateUrl('prof_deputy_costs_summary', ['reportId' => $reportId]));
        }

        if ($from === 'summary') {
            $backLink = 'prof_deputy_costs_summary';
        } else if ($report->hasProfDeputyCostsHowChargedFixedOnly()) {
            $backLink = 'prof_deputy_costs_received';
        } else {
            $backLink = 'prof_deputy_costs_amount_scco';
        }

        return [
            'backLink' =>$this->generateUrl( $backLink, ['reportId'=>$reportId]),
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
    private function generateDefaultOtherCosts(EntityDir\Report\Report $report)
    {
        $otherCosts = [];

        $defaultOtherCostTypeIds = $report->getProfDeputyOtherCostTypeIds();
        foreach ($defaultOtherCostTypeIds as $defaultOtherCostType) {
            $otherCosts[] = new EntityDir\Report\ProfDeputyOtherCost(
                $defaultOtherCostType['typeId'],
                null,
                $defaultOtherCostType['hasMoreDetails'],
                null
            );

        }
        return $otherCosts;
    }

    /**
     * @Route("/summary", name="prof_deputy_costs_summary")
     * @Template("AppBundle:Report/ProfDeputyCosts:summary.html.twig")
     *
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function summaryAction($reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getProfDeputyCostsState()['state'] == EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirect($this->generateUrl('prof_deputy_costs', ['reportId' => $reportId]));
        }

        return [
            'submittedOtherCosts' => $report->generateActualSubmittedOtherCosts(),
            'report' => $report,
        ];
    }


    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'profDeputyCosts';
    }
}
