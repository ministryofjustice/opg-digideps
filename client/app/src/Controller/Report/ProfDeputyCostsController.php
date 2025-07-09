<?php

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Form as FormDir;
use App\Resolver\SubSectionRoute\ProfCostsSubSectionRouteResolver;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Base route.
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
        'prof-deputy-other-costs',
    ];

    public function __construct(
        private readonly RestClient $restClient,
        private readonly ReportApi $reportApi,
    ) {
    }

    /**
     * @Route("", name="prof_deputy_costs")
     *
     * @Template("@App/Report/ProfDeputyCosts/start.html.twig")
     */
    public function startAction(int $reportId, ProfCostsSubSectionRouteResolver $routeResolver): array|RedirectResponse
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
     *
     * @Template("@App/Report/ProfDeputyCosts/howCharged.html.twig")
     */
    public function howChargedAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $from = $request->get('from');

        $form = $this->createForm(FormDir\Report\ProfDeputyCostHowType::class, $report);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->restClient->put('report/'.$reportId, $data, ['deputyCostsHowCharged']);

            if ('summary' === $from) {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
                $nextRoute = 'prof_deputy_costs_summary';
            } else {
                $nextRoute = 'prof_deputy_costs_previous_received_exists';
            }

            return $this->redirectToRoute($nextRoute, ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
            'backLink' => $this->generateUrl('summary' === $from ? 'prof_deputy_costs_summary' : 'prof_deputy_costs', ['reportId' => $reportId]),
        ];
    }

    /**
     * @Route("/previous-received-exists", name="prof_deputy_costs_previous_received_exists")
     *
     * @Template("@App/Report/ProfDeputyCosts/previousReceivedExists.html.twig")
     */
    public function previousReceivedExists(Request $request, int $reportId): array|RedirectResponse
    {
        $from = $request->get('from', 'exist');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(
            FormDir\YesNoType::class,
            $report,
            [
                'field' => 'profDeputyCostsHasPrevious',
                'translation_domain' => 'report-prof-deputy-costs',
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Report */
            switch ($data->getProfDeputyCostsHasPrevious()) {
                case 'yes':
                    // no need to save. "Yes" will be set when one entry is added to keep db data consistent
                    return $this->redirectToRoute('prof_deputy_costs_previous_received', ['reportId' => $reportId, 'from' => $from]);
                case 'no':
                    // store and go to next route
                    $this->restClient->put('report/'.$reportId, $data, ['profDeputyCostsHasPrevious']);

                    if ('summary' == $from) {
                        $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
                        $nextRoute = 'prof_deputy_costs_summary';
                    } elseif ($report->hasProfDeputyCostsHowChargedFixedOnly()) {
                        $nextRoute = 'prof_deputy_costs_received';
                    } else {
                        $nextRoute = 'prof_deputy_costs_inline_interim_19b_exists';
                    }

                    return $this->redirectToRoute($nextRoute, ['reportId' => $reportId]);
            }
        }

        return [
            // previous step could be interim or fixed. easier NOT showing any backlink
            'backLink' => $this->generateUrl('summary' === $from ? 'prof_deputy_costs_summary' : 'prof_deputy_costs_how_charged', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/previous-received/{previousReceivedId}", name="prof_deputy_costs_previous_received")
     *
     * @Template("@App/Report/ProfDeputyCosts/previousReceived.html.twig")
     */
    public function previousReceived(Request $request, int $reportId, ?int $previousReceivedId = null): array|RedirectResponse
    {
        $from = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        // create (add mode) or load transaction (edit mode)
        if (is_null($previousReceivedId)) {
            $pr = new EntityDir\Report\ProfDeputyPreviousCost();
        } else {
            /** @var EntityDir\Report\ProfDeputyPreviousCost $pr */
            $pr = $this->restClient->get('/prof-deputy-previous-cost/'.$previousReceivedId, 'Report\\ProfDeputyPreviousCost');
        }

        $form = $this->createForm(FormDir\Report\ProfDeputyCostPreviousType::class, $pr, [
            'editMode' => !empty($previousReceivedId),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($previousReceivedId) { // edit
                $this->restClient->put('/prof-deputy-previous-cost/'.$previousReceivedId, $pr, ['profDeputyPrevCosts']);
                $request->getSession()->getFlashBag()->add('notice', 'Cost edited');
            } else {
                $this->restClient->post('/report/'.$reportId.'/prof-deputy-previous-cost', $pr, ['profDeputyPrevCosts']);
                $request->getSession()->getFlashBag()->add('notice', 'Cost added');
            }

            if ('saveAndAddAnother' === $form->getClickedButton()->getName()) {
                $nextRoute = 'prof_deputy_costs_previous_received';
            } elseif ('summary' === $from) {
                $nextRoute = 'prof_deputy_costs_summary';
            } elseif ($report->hasProfDeputyCostsHowChargedFixedOnly()) {
                $nextRoute = 'prof_deputy_costs_received';
            } else {
                $nextRoute = 'prof_deputy_costs_inline_interim_19b_exists';
            }

            return $this->redirectToRoute($nextRoute, ['reportId' => $reportId]);
        }

        return [
            'backLink' => $this->generateUrl('summary' == $from ? 'prof_deputy_costs_summary' : 'prof_deputy_costs_previous_received_exists', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/previous-received/{previousReceivedId}/delete", name="prof_deputy_costs_previous_received_delete")
     *
     * @Template("@App/Common/confirmDelete.html.twig")
     */
    public function previousCostDelete(Request $request, int $reportId, int $previousReceivedId): array|RedirectResponse
    {
        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->delete('report/'.$report->getId().'/prof-deputy-previous-cost/'.$previousReceivedId);

            $request->getSession()->getFlashBag()->add(
                'notice',
                'Cost deleted'
            );

            return $this->redirect($this->generateUrl('prof_deputy_costs_summary', ['reportId' => $reportId]));
        }

        $cost = $this->restClient->get('/prof-deputy-previous-cost/'.$previousReceivedId, 'Report\ProfDeputyPreviousCost');

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
     *
     * @Template("@App/Report/ProfDeputyCosts/interimExists.html.twig")
     */
    public function interimExists(Request $request, int $reportId): array|RedirectResponse
    {
        $from = $request->get('from', 'exist');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(
            FormDir\YesNoType::class,
            $report,
            [
                'field' => 'profDeputyCostsHasInterim',
                'translation_domain' => 'report-prof-deputy-costs',
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Report */

            // store yes or no
            $this->restClient->put('report/'.$reportId, $data, ['profDeputyCostsHasInterim']);

            // next route calculation
            switch ($data->getProfDeputyCostsHasInterim()) {
                case 'yes':
                    // go to interim page, and pass by the "from"
                    return $this->redirectToRoute('prof_deputy_costs_inline_interim_19b', ['reportId' => $reportId, 'from' => $from]);
                case 'no':
                    if ('summary' === $from) {
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
     *
     * @Template("@App/Report/ProfDeputyCosts/interim.html.twig")
     */
    public function interim(Request $request, int $reportId): array|RedirectResponse
    {
        $from = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        // fill missing interim with empty entities, in order for 3 subforms in total to appear
        for ($i = count($report->getProfDeputyInterimCosts()); $i < 3; ++$i) {
            $report->addProfDeputyInterimCosts(new EntityDir\Report\ProfDeputyInterimCost());
        }

        $form = $this->createForm(FormDir\Report\ProfDeputyCostInterimType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->put('/report/'.$reportId, $report, ['profDeputyInterimCosts']);

            if ('summary' === $from) {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
                $nextRoute = 'prof_deputy_costs_summary';
            } else { // saveAndContinue
                $nextRoute = 'prof_deputy_costs_amount_scco';
            }

            return $this->redirectToRoute($nextRoute, ['reportId' => $reportId]);
        }

        return [
            'backLink' => $this->generateUrl('summary' == $from ? 'prof_deputy_costs_summary' : 'prof_deputy_costs_inline_interim_19b_exists', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/costs-received", name="prof_deputy_costs_received")
     *
     * @Template("@App/Report/ProfDeputyCosts/fixedCost.html.twig")
     */
    public function fixedCostAction(Request $request, int $reportId): array|RedirectResponse
    {
        $from = $request->get('from');
        /** @var EntityDir\Report\Report $report */
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(FormDir\Report\ProfDeputyFixedCostType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->put('/report/'.$reportId, $report, ['profDeputyFixedCost']);

            if ('summary' === $from) {
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
            'backLink' => 'summary' == $from ? $this->generateUrl('prof_deputy_costs_summary', ['reportId' => $reportId]) : null,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/amount-scco", name="prof_deputy_costs_amount_scco")
     *
     * @Template("@App/Report/ProfDeputyCosts/amountToScco.html.twig")
     */
    public function amountToSccoAction(Request $request, int $reportId): array|RedirectResponse
    {
        $from = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(FormDir\Report\ProfDeputyCostSccoType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->put('/report/'.$reportId, $report, ['profDeputyCostsScco']);

            if ('summary' === $from) {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
                $nextRoute = 'prof_deputy_costs_summary';
            } else {
                $nextRoute = 'prof_deputy_costs_breakdown';
            }

            return $this->redirectToRoute($nextRoute, ['reportId' => $reportId]);
        }

        return [
            // backlink depends on "fixed" being selected. Simpler not to show a backlink unless necessary
            'backLink' => 'summary' == $from ? $this->generateUrl('prof_deputy_costs_summary', ['reportId' => $reportId]) : null,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/breakdown", name="prof_deputy_costs_breakdown")
     *
     * @Template("@App/Report/ProfDeputyCosts/breakdown.html.twig")
     */
    public function breakdown(Request $request, int $reportId): array|RedirectResponse
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
            /** @var EntityDir\Report\Report $report */
            $report = $form->getData();

            $this->restClient->put('report/'.$report->getId(), $report, ['prof-deputy-other-costs']);

            if ('summary' === $from) {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
            }

            return $this->redirect($this->generateUrl('prof_deputy_costs_summary', ['reportId' => $reportId]));
        }

        if ('summary' === $from) {
            $backLink = 'prof_deputy_costs_summary';
        } elseif ($report->hasProfDeputyCostsHowChargedFixedOnly()) {
            $backLink = 'prof_deputy_costs_received';
        } else {
            $backLink = 'prof_deputy_costs_amount_scco';
        }

        return [
            'backLink' => $this->generateUrl($backLink, ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * Retrieves the list of default other cost type IDs using virtual property from api
     * Used to generate the page since with no initial data, we cant display form inputs
     * without this list.
     */
    private function generateDefaultOtherCosts(EntityDir\Report\Report $report): array
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
     *
     * @Template("@App/Report/ProfDeputyCosts/summary.html.twig")
     */
    public function summaryAction(int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (EntityDir\Report\Status::STATE_NOT_STARTED == $report->getStatus()->getProfDeputyCostsState()['state']) {
            return $this->redirect($this->generateUrl('prof_deputy_costs', ['reportId' => $reportId]));
        }

        return [
            'submittedOtherCosts' => $report->generateActualSubmittedOtherCosts(),
            'report' => $report,
        ];
    }
}
