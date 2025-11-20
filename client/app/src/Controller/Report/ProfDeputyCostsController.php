<?php

declare(strict_types=1);

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity\Report\ProfDeputyInterimCost;
use App\Entity\Report\ProfDeputyOtherCost;
use App\Entity\Report\ProfDeputyPreviousCost;
use App\Entity\Report\Report;
use App\Entity\Report\Status;
use App\Form\ConfirmDeleteType;
use App\Form\Report\ProfDeputyCostHowType;
use App\Form\Report\ProfDeputyCostInterimType;
use App\Form\Report\ProfDeputyCostPreviousType;
use App\Form\Report\ProfDeputyCostSccoType;
use App\Form\Report\ProfDeputyFixedCostType;
use App\Form\Report\ProfDeputyOtherCostsType;
use App\Form\YesNoType;
use App\Resolver\SubSectionRoute\ProfCostsSubSectionRouteResolver;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/report/{reportId}/prof-deputy-costs')]
class ProfDeputyCostsController extends AbstractController
{
    private static array $jmsGroups = [
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
        private readonly ReportApi $reportApi
    ) {
    }

    #[Route(path: '', name: 'prof_deputy_costs')]
    #[Template('@App/Report/ProfDeputyCosts/start.html.twig')]
    public function startAction(int $reportId, ProfCostsSubSectionRouteResolver $routeResolver): RedirectResponse|array
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

    #[Route(path: '/how-charged', name: 'prof_deputy_costs_how_charged')]
    #[Template('@App/Report/ProfDeputyCosts/howCharged.html.twig')]
    public function howChargedAction(Request $request, int $reportId): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $from = $request->get('from');

        $form = $this->createForm(ProfDeputyCostHowType::class, $report);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->restClient->put('report/' . $reportId, $data, ['deputyCostsHowCharged']);

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

    #[Route(path: '/previous-received-exists', name: 'prof_deputy_costs_previous_received_exists')]
    #[Template('@App/Report/ProfDeputyCosts/previousReceivedExists.html.twig')]
    public function previousReceivedExists(Request $request, int $reportId): RedirectResponse|array
    {
        $from = $request->get('from', 'exist');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(
            YesNoType::class,
            $report,
            [
            'field' => 'profDeputyCostsHasPrevious',
            'translation_domain' => 'report-prof-deputy-costs',
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* @var Report $data */
            $data = $form->getData();

            switch ($data->getProfDeputyCostsHasPrevious()) {
                case 'yes':
                    // no need to save. "Yes" will be set when one entry is added to keep db data consistent
                    return $this->redirectToRoute('prof_deputy_costs_previous_received', ['reportId' => $reportId, 'from' => $from]);
                case 'no':
                    // store and go to next route
                    $this->restClient->put('report/' . $reportId, $data, ['profDeputyCostsHasPrevious']);

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

    #[Route(path: '/previous-received/{previousReceivedId}', name: 'prof_deputy_costs_previous_received')]
    #[Template('@App/Report/ProfDeputyCosts/previousReceived.html.twig')]
    public function previousReceived(Request $request, int $reportId, ?int $previousReceivedId = null): RedirectResponse|array
    {
        $from = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        // create (add mode) or load transaction (edit mode)
        if ($previousReceivedId) {
            /** @var ProfDeputyPreviousCost $pr */
            $pr = $this->restClient->get("/prof-deputy-previous-cost/$previousReceivedId", 'Report\\ProfDeputyPreviousCost');
        } else {
            $pr = new ProfDeputyPreviousCost();
        }

        $form = $this->createForm(ProfDeputyCostPreviousType::class, $pr, [
            'editMode' => !empty($previousReceivedId),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($previousReceivedId) { // edit
                $this->restClient->put('/prof-deputy-previous-cost/' . $previousReceivedId, $pr, ['profDeputyPrevCosts']);
                $request->getSession()->getFlashBag()->add('notice', 'Cost edited');
            } else {
                $this->restClient->post("/report/$reportId/prof-deputy-previous-cost", $pr, ['profDeputyPrevCosts']);
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

    #[Route(path: '/previous-received/{previousReceivedId}/delete', name: 'prof_deputy_costs_previous_received_delete')]
    #[Template('@App/Common/confirmDelete.html.twig')]
    public function previousCostDelete(Request $request, int $reportId, int $previousReceivedId): RedirectResponse|array
    {
        $form = $this->createForm(ConfirmDeleteType::class);
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

    #[Route(path: '/interim-exists', name: 'prof_deputy_costs_inline_interim_19b_exists')]
    #[Template('@App/Report/ProfDeputyCosts/interimExists.html.twig')]
    public function interimExists(Request $request, int $reportId): RedirectResponse|array
    {
        $from = $request->get('from', 'exist');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(
            YesNoType::class,
            $report,
            [
                'field' => 'profDeputyCostsHasInterim',
                'translation_domain' => 'report-prof-deputy-costs',
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* @var Report $data */
            $data = $form->getData();

            // store yes or no
            $this->restClient->put("report/$reportId", $data, ['profDeputyCostsHasInterim']);

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

    #[Route(path: '/interim', name: 'prof_deputy_costs_inline_interim_19b')]
    #[Template('@App/Report/ProfDeputyCosts/interim.html.twig')]
    public function interim(Request $request, int $reportId): RedirectResponse|array
    {
        $from = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        // fill missing interim with empty entities, in order for 3 subforms in total to appear
        for ($i = count($report->getProfDeputyInterimCosts()); $i < 3; ++$i) {
            $report->addProfDeputyInterimCosts(new ProfDeputyInterimCost());
        }

        $form = $this->createForm(ProfDeputyCostInterimType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->put('/report/' . $reportId, $report, ['profDeputyInterimCosts']);

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

    #[Route(path: '/costs-received', name: 'prof_deputy_costs_received')]
    #[Template('@App/Report/ProfDeputyCosts/fixedCost.html.twig')]
    public function fixedCostAction(Request $request, string $reportId): RedirectResponse|array
    {
        $from = $request->get('from');

        $report = $this->reportApi->getReportIfNotSubmitted(intval($reportId), self::$jmsGroups);

        $form = $this->createForm(ProfDeputyFixedCostType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->put('/report/' . $reportId, $report, ['profDeputyFixedCost']);

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

    #[Route(path: '/amount-scco', name: 'prof_deputy_costs_amount_scco')]
    #[Template('@App/Report/ProfDeputyCosts/amountToScco.html.twig')]
    public function amountToSccoAction(Request $request, string $reportId): RedirectResponse|array
    {
        $from = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted(intval($reportId), self::$jmsGroups);

        $form = $this->createForm(ProfDeputyCostSccoType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->put("/report/$reportId", $report, ['profDeputyCostsScco']);

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

    #[Route(path: '/breakdown', name: 'prof_deputy_costs_breakdown')]
    #[Template('@App/Report/ProfDeputyCosts/breakdown.html.twig')]
    public function breakdown(Request $request, int $reportId): RedirectResponse|array
    {
        $from = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (empty($report->getProfDeputyOtherCosts())) {
            // if none set generate other costs manually
            $otherCosts = $this->generateDefaultOtherCosts($report);

            $report->setProfDeputyOtherCosts($otherCosts);
        }

        $form = $this->createForm(ProfDeputyOtherCostsType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ProfDeputyOtherCost $data */
            $data = $form->getData();

            $this->restClient->put('report/' . $report->getId(), $data, ['prof-deputy-other-costs']);

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
    private function generateDefaultOtherCosts(Report $report): array
    {
        $otherCosts = [];

        $defaultOtherCostTypeIds = $report->getProfDeputyOtherCostTypeIds();
        foreach ($defaultOtherCostTypeIds as $defaultOtherCostType) {
            $otherCosts[] = new ProfDeputyOtherCost(
                $defaultOtherCostType['typeId'],
                null,
                $defaultOtherCostType['hasMoreDetails'],
                null
            );
        }

        return $otherCosts;
    }

    #[Route(path: '/summary', name: 'prof_deputy_costs_summary')]
    #[Template('@App/Report/ProfDeputyCosts/summary.html.twig')]
    public function summaryAction(int $reportId): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (Status::STATE_NOT_STARTED == $report->getStatus()->getProfDeputyCostsState()['state']) {
            return $this->redirect($this->generateUrl('prof_deputy_costs', ['reportId' => $reportId]));
        }

        return [
            'submittedOtherCosts' => $report->generateActualSubmittedOtherCosts(),
            'report' => $report,
        ];
    }

    protected function getSectionId(): string
    {
        return 'profDeputyCosts';
    }
}
