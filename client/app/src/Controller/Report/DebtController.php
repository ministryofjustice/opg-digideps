<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Controller\Report;

use OPG\Digideps\Frontend\Controller\AbstractController;
use OPG\Digideps\Frontend\Entity\Report\Status;
use OPG\Digideps\Frontend\Form\Report\Debt\DebtManagementType;
use OPG\Digideps\Frontend\Form\Report\Debt\DebtsType;
use OPG\Digideps\Frontend\Form\YesNoType;
use OPG\Digideps\Frontend\Service\Client\Internal\ReportApi;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DebtController extends AbstractController
{
    private static array $jmsGroups = [
        'debt',
        'debt-state',
        'debt-management',
    ];

    public function __construct(
        private readonly RestClient $restClient,
        private readonly ReportApi $reportApi,
    ) {}

    #[Route(path: '/report/{reportId}/debts', name: 'debts')]
    #[Template('@App/Report/Debt/start.html.twig')]
    public function startAction(int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getDebtsState()['state'] != Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('debts_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    #[Route(path: '/report/{reportId}/debts/exist', name: 'debts_exist')]
    #[Template('@App/Report/Debt/exist.html.twig')]
    public function existAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(
            YesNoType::class,
            $report,
            ['field' => 'hasDebts', 'translation_domain' => 'report-debts']
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->put('report/' . $reportId, $report, ['debt']);

            if ($report->getHasDebts() == 'yes') {
                return $this->redirectToRoute('debts_edit', ['reportId' => $reportId]);
            }

            return $this->redirectToRoute('debts_summary', ['reportId' => $reportId]);
        }

        $backLink = $this->generateUrl('debts', ['reportId' => $reportId]);
        if ($request->get('from') == 'summary') {
            $backLink = $this->generateUrl('debts_summary', ['reportId' => $reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * List debts.
     */
    #[Route(path: '/report/{reportId}/debts/edit', name: 'debts_edit')]
    #[Template('@App/Report/Debt/edit.html.twig')]
    public function editAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(DebtsType::class, $report);
        $form->handleRequest($request);

        $fromPage = $request->get('from');

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->put('report/' . $report->getId(), $form->getData(), ['debt']);

            if ($fromPage == 'summary') {
                if (empty($report->getDebtManagement())) {
                    return $this->redirect($this->generateUrl('debts_management', ['reportId' => $reportId, 'from' => 'summary']));
                }
                $request->getSession()->getFlashBag()->add('notice', 'Debt edited');

                return $this->redirect($this->generateUrl('debts_summary', ['reportId' => $reportId, 'from' => 'summary']));
            }

            return $this->redirect($this->generateUrl('debts_management', ['reportId' => $reportId]));
        }

        $backLink = $this->generateUrl('debts_exist', ['reportId' => $reportId]);
        if ($fromPage == 'summary') {
            $backLink = $this->generateUrl('debts_summary', ['reportId' => $reportId]);
        }

        return [
            'backLink' => $backLink,
            'report' => $report,
            'form' => $form->createView(),
        ];
    }

    /**
     * How debts are managed question.
     */
    #[Route(path: '/report/{reportId}/debts/management', name: 'debts_management')]
    #[Template('@App/Report/Debt/management.html.twig')]
    public function managementAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(DebtManagementType::class, $report);
        $form->handleRequest($request);
        $fromPage = $request->get('from');
        $fromSummaryPage = $request->get('from') == 'summary';

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->put('report/' . $report->getId(), $form->getData(), ['debt-management']);

            if ($fromPage == 'summary') {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
            }

            return $this->redirect($this->generateUrl('debts_summary', ['reportId' => $reportId]));
        }

        $backLink = $this->generateUrl('debts_exist', ['reportId' => $reportId]);
        if ($fromPage == 'summary') {
            $backLink = $this->generateUrl('debts_summary', ['reportId' => $reportId]);
        }

        return [
            'backLink' => $backLink,
            'skipLink' => $fromSummaryPage ? null : $this->generateUrl('debts_summary', ['reportId' => $report->getId()]),
            'report' => $report,
            'form' => $form->createView(),
        ];
    }

    /**
     * List debts.
     */
    #[Route(path: '/report/{reportId}/debts/summary', name: 'debts_summary')]
    #[Template('@App/Report/Debt/summary.html.twig')]
    public function summaryAction(Request $request, int $reportId): array|RedirectResponse
    {
        $fromPage = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getDebtsState()['state'] == Status::STATE_NOT_STARTED && $fromPage != 'skip-step') {
            return $this->redirectToRoute('debts', ['reportId' => $reportId]);
        }

        return [
            'comingFromLastStep' => $fromPage == 'skip-step' || $fromPage == 'last-step',
            'report' => $report,
            'status' => $report->getStatus(),
        ];
    }
}
