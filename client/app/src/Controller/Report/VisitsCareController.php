<?php

declare(strict_types=1);

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity\Report\Status;
use App\Entity\Report\VisitsCare;
use App\Form\Report\VisitsCareType;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\StepRedirector;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class VisitsCareController extends AbstractController
{
    private static array $jmsGroups = [
        'visits-care',
        'visits-care-state',
    ];

    public function __construct(
        private readonly RestClient $restClient,
        private readonly ReportApi $reportApi,
        private readonly StepRedirector $stepRedirector,
    ) {
    }

    #[Route(path: '/report/{reportId}/visits-care', name: 'visits_care')]
    #[Template('@App/Report/VisitsCare/start.html.twig')]
    public function startAction(Request $request, int $reportId): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (Status::STATE_NOT_STARTED != $report->getStatus()->getVisitsCareState()['state']) {
            return $this->redirectToRoute('visits_care_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    #[Route(path: '/report/{reportId}/visits-care/step/{step}', name: 'visits_care_step')]
    #[Template('@App/Report/VisitsCare/step.html.twig')]
    public function stepAction(Request $request, int $reportId, $step, TranslatorInterface $translator): RedirectResponse|array
    {
        $totalSteps = 4;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('visits_care_summary', ['reportId' => $reportId]);
        }
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $visitsCare = $report->getVisitsCare() ?: new VisitsCare();
        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector
            ->setRoutes('visits_care', 'visits_care_step', 'visits_care_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId' => $reportId]);

        $form = $this->createForm(
            VisitsCareType::class,
            $visitsCare,
            [
                'step' => $step,
                'translator' => $translator,
                'clientFirstName' => $report->getClient()->getFirstname(),
            ]
        );

        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            /** @var VisitsCare $data */
            $data = $form->getData();

            $data
                ->setReport($report)
                ->keepOnlyRelevantVisitsCareData();

            if (null == $visitsCare->getId()) {
                $this->restClient->post('report/visits-care', $data, ['visits-care', 'report-id']);
            } else {
                $this->restClient->put('report/visits-care/' . $visitsCare->getId(), $data, self::$jmsGroups);
            }

            if ('summary' == $fromPage) {
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'Answer edited'
                );
            }

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }

        return [
            'report' => $report,
            'step' => $step,
            'reportStatus' => $report->getStatus(),
            'form' => $form->createView(),
            'backLink' => $stepRedirector->getBackLink(),
            'skipLink' => $stepRedirector->getSkipLink(),
        ];
    }

    #[Route(path: '/report/{reportId}/visits-care/summary', name: 'visits_care_summary')]
    #[Template('@App/Report/VisitsCare/summary.html.twig')]
    public function summaryAction(Request $request, int $reportId): RedirectResponse|array
    {
        $fromPage = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (Status::STATE_NOT_STARTED == $report->getStatus()->getVisitsCareState()['state'] && 'skip-step' != $fromPage) {
            return $this->redirectToRoute('visits_care', ['reportId' => $reportId]);
        }

        if (!$report->getVisitsCare()) { // allow validation with answers all skipped
            $report->setVisitsCare(new VisitsCare());
        }

        return [
            'comingFromLastStep' => 'skip-step' == $fromPage || 'last-step' == $fromPage,
            'report' => $report,
            'status' => $report->getStatus(),
        ];
    }

    protected function getSectionId(): string
    {
        return 'visitsCare';
    }
}
