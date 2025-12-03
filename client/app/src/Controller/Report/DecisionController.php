<?php

declare(strict_types=1);

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity\Report\Decision;
use App\Entity\Report\MentalCapacity;
use App\Entity\Report\Status;
use App\Form\ConfirmDeleteType;
use App\Form\Report\DecisionExistType;
use App\Form\Report\DecisionType;
use App\Form\Report\MentalAssessment;
use App\Form\Report\MentalCapacityType;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class DecisionController extends AbstractController
{
    private static array $jmsGroups = [
        'decision',
        'mental-capacity',
        'decision-status',
        'significantDecisionMade',
    ];

    public function __construct(
        private readonly RestClient $restClient,
        private readonly ReportApi $reportApi,
    ) {
    }

    #[Route(path: '/report/{reportId}/decisions', name: 'decisions')]
    #[Template('@App/Report/Decision/start.html.twig')]
    public function startAction(int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (Status::STATE_NOT_STARTED != $report->getStatus()->getDecisionsState()['state']) {
            return $this->redirectToRoute('decisions_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    #[Route(path: '/report/{reportId}/decisions/mental-capacity', name: 'decisions_mental_capacity')]
    #[Template('@App/Report/Decision/mentalCapacity.html.twig')]
    public function mentalCapacityAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromSummaryPage = 'summary' == $request->get('from');

        $mc = $report->getMentalCapacity();
        if (null == $mc) {
            $mc = new MentalCapacity();
        }

        $form = $this->createForm(MentalCapacityType::class, $mc);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->restClient->put('report/' . $reportId . '/mental-capacity', $data, ['mental-capacity']);
            if ($fromSummaryPage) {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
            }

            return $this->redirectToRoute($fromSummaryPage ? 'decisions_summary' : 'decisions_mental_assessment', ['reportId' => $reportId]);
        }

        return [
            'form' => $form->createView(),
            'backLink' => $this->generateUrl($fromSummaryPage ? 'decisions_summary' : 'decisions', ['reportId' => $report->getId()]),
            'skipLink' => $fromSummaryPage ? null : $this->generateUrl('decisions_mental_assessment', ['reportId' => $report->getId()]),
            'report' => $report,
        ];
    }

    #[Route(path: '/report/{reportId}/decisions/mental-assessment', name: 'decisions_mental_assessment')]
    #[Template('@App/Report/Decision/mentalAssessment.html.twig')]
    public function mentalAssessmentAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromSummaryPage = 'summary' == $request->get('from');

        $mc = $report->getMentalCapacity();
        if (null == $mc) {
            $mc = new MentalCapacity();
        }

        $form = $this->createForm(MentalAssessment::class, $mc);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->restClient->put('report/' . $reportId . '/mental-capacity', $data, ['mental-assessment-date']);
            if ($fromSummaryPage) {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
            }

            return $this->redirectToRoute($fromSummaryPage ? 'decisions_summary' : 'decisions_exist', ['reportId' => $reportId]);
        }

        return [
            'form' => $form->createView(),
            'backLink' => $this->generateUrl($fromSummaryPage ? 'decisions_summary' : 'decisions_mental_capacity', ['reportId' => $report->getId()]),
            'skipLink' => $fromSummaryPage ? null : $this->generateUrl('decisions_exist', ['reportId' => $report->getId()]),
            'report' => $report,
        ];
    }

    #[Route(path: '/report/{reportId}/decisions/exist', name: 'decisions_exist')]
    #[Template('@App/Report/Decision/exist.html.twig')]
    public function existAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(DecisionExistType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $report = $form->getData();

            /** @var Form $significantDecisionsMade */
            $significantDecisionsMade = $form['significantDecisionsMade'];
            $answer = $significantDecisionsMade->getData();

            if ('Yes' == $answer) {
                $report->setReasonForNoDecisions(null);

                $this->updateReport($report, $reportId);

                return $this->redirectToRoute('decisions_add', ['reportId' => $reportId, 'from' => 'decisions_exist']);
            } else {
                foreach ($report->getDecisions() as $decision) {
                    $this->restClient->delete('/report/decision/' . $decision->getId());
                }

                // this must proceed the deletion above if deputy switches from 'yes' to 'no' as it will not persist the 'reason for decision' answer
                $this->updateReport($report, $reportId);

                return $this->redirectToRoute('decisions_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = $this->generateUrl('decisions_mental_assessment', ['reportId' => $reportId]);
        if ('summary' == $request->get('from')) {
            $backLink = $this->generateUrl('decisions_summary', ['reportId' => $reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    private function updateReport($report, int $reportId): void
    {
        $fields = ['significantDecisionsMade', 'reasonForNoDecisions'];
        $this->restClient->put('report/' . $reportId, $report, $fields);
    }

    #[Route(path: '/report/{reportId}/decisions/add', name: 'decisions_add')]
    #[Template('@App/Report/Decision/add.html.twig')]
    public function addAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $decision = new Decision();

        $form = $this->createForm(DecisionType::class, $decision);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->restClient->post('report/decision', $data, ['decision', 'report-id']);

            /** @var Form $addAnother */
            $addAnother = $form['addAnother'];
            switch ($addAnother->getData()) {
                case 'yes':
                    return $this->redirectToRoute('decisions_add', ['reportId' => $reportId]);
                case 'no':
                    return $this->redirectToRoute('decisions_summary', ['reportId' => $reportId]);
            }
        }

        // TODO use $backLinkRoute logic and align to other controllers
        try {
            $backLink = $this->generateUrl('decisions_summary', ['reportId' => $reportId]);

            return [
                'backLink' => $backLink,
                'form' => $form->createView(),
                'report' => $report,
            ];
        } catch (RouteNotFoundException) {
            return [
                'backLink' => null,
                'form' => $form->createView(),
                'report' => $report,
            ];
        }
    }

    #[Route(path: '/report/{reportId}/decisions/edit/{decisionId}', name: 'decisions_edit')]
    #[Template('@App/Report/Decision/edit.html.twig')]
    public function editAction(Request $request, int $reportId, int $decisionId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $decision = $this->restClient->get('report/decision/' . $decisionId, 'Report\\Decision');
        $decision->setReport($report);

        $form = $this->createForm(DecisionType::class, $decision);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->restClient->put('report/decision', $data, ['decision']);

            $request->getSession()->getFlashBag()->add('notice', 'Decision edited');

            /** @var Form $addAnother */
            $addAnother = $form['addAnother'];
            switch ($addAnother->getData()) {
                case 'yes':
                    return $this->redirectToRoute('decisions_add', ['reportId' => $reportId]);
                case 'no':
                    return $this->redirectToRoute('decisions_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'backLink' => $this->generateUrl('decisions_summary', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    #[Route(path: '/report/{reportId}/decisions/summary', name: 'decisions_summary')]
    #[Template('@App/Report/Decision/summary.html.twig')]
    public function summaryAction(Request $request, int $reportId): array|RedirectResponse
    {
        $fromPage = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (Status::STATE_NOT_STARTED == $report->getStatus()->getDecisionsState()['state'] && 'skip-step' != $fromPage) {
            return $this->redirectToRoute('decisions', ['reportId' => $reportId]);
        }

        $numberOfDecisions = count($report->getDecisions());

        return [
            'comingFromLastStep' => 'skip-step' == $fromPage || 'last-step' == $fromPage,
            'report' => $report,
            'status' => $report->getStatus(),
            'numOfDecisions' => $numberOfDecisions,
        ];
    }

    #[Route(path: '/report/{reportId}/decisions/{decisionId}/delete', name: 'decisions_delete')]
    #[Template('@App/Common/confirmDelete.html.twig')]
    public function deleteAction(Request $request, int $reportId, int $decisionId): array|RedirectResponse
    {
        $form = $this->createForm(ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->delete("/report/decision/$decisionId");

            $request->getSession()->getFlashBag()->add(
                'notice',
                'Decision deleted'
            );

            return $this->redirect($this->generateUrl('decisions', ['reportId' => $reportId]));
        }

        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $decision = $this->restClient->get('report/decision/' . $decisionId, 'Report\\Decision');

        return [
            'translationDomain' => 'report-decisions',
            'report' => $report,
            'form' => $form->createView(),
            'summary' => [
                ['label' => 'deletePage.summary.description', 'value' => $decision->getDescription()],
            ],
            'backLink' => $this->generateUrl('decisions', ['reportId' => $reportId]),
        ];
    }
}
