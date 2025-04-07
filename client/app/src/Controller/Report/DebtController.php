<?php

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Form as FormDir;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DebtController extends AbstractController
{
    private static $jmsGroups = [
        'debt',
        'debt-state',
        'debt-management',
    ];

    public function __construct(
        private RestClient $restClient,
        private ReportApi $reportApi,
        private ClientApi $clientApi,
    ) {
    }

    /**
     * @Route("/report/{reportId}/debts", name="debts")
     *
     * @Template("@App/Report/Debt/start.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function startAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (EntityDir\Report\Status::STATE_NOT_STARTED != $report->getStatus()->getDebtsState()['state']) {
            return $this->redirectToRoute('debts_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/debts/exist", name="debts_exist")
     *
     * @Template("@App/Report/Debt/exist.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(
            FormDir\YesNoType::class,
            $report,
            ['field' => 'hasDebts', 'translation_domain' => 'report-debts']
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->put('report/'.$reportId, $report, ['debt']);

            if ('yes' == $report->getHasDebts()) {
                return $this->redirectToRoute('debts_edit', ['reportId' => $reportId]);
            }

            return $this->redirectToRoute('debts_summary', ['reportId' => $reportId]);
        }

        $backLink = $this->generateUrl('debts', ['reportId' => $reportId]);
        if ('summary' == $request->get('from')) {
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
     *
     * @Route("/report/{reportId}/debts/edit", name="debts_edit")
     *
     * @Template("@App/Report/Debt/edit.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function editAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(FormDir\Report\Debt\DebtsType::class, $report);
        $form->handleRequest($request);

        $fromPage = $request->get('from');

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->put('report/'.$report->getId(), $form->getData(), ['debt']);

            if ('summary' == $fromPage) {
                if (empty($report->getDebtManagement())) {
                    return $this->redirect($this->generateUrl('debts_management', ['reportId' => $reportId, 'from' => 'summary']));
                }
                $request->getSession()->getFlashBag()->add('notice', 'Debt edited');

                return $this->redirect($this->generateUrl('debts_summary', ['reportId' => $reportId, 'from' => 'summary']));
            }

            return $this->redirect($this->generateUrl('debts_management', ['reportId' => $reportId]));
        }

        $backLink = $this->generateUrl('debts_exist', ['reportId' => $reportId]);
        if ('summary' == $fromPage) {
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
     *
     * @Route("/report/{reportId}/debts/management", name="debts_management")
     *
     * @Template("@App/Report/Debt/management.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function managementAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Report\Debt\DebtManagementType::class, $report);
        $form->handleRequest($request);
        $fromPage = $request->get('from');
        $fromSummaryPage = 'summary' == $request->get('from');

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->put('report/'.$report->getId(), $form->getData(), ['debt-management']);

            if ('summary' == $fromPage) {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
            }

            return $this->redirect($this->generateUrl('debts_summary', ['reportId' => $reportId]));
        }

        $backLink = $this->generateUrl('debts_exist', ['reportId' => $reportId]);
        if ('summary' == $fromPage) {
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
     *
     * @Route("/report/{reportId}/debts/summary", name="debts_summary")
     *
     * @Template("@App/Report/Debt/summary.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function summaryAction(Request $request, $reportId)
    {
        $fromPage = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (EntityDir\Report\Status::STATE_NOT_STARTED == $report->getStatus()->getDebtsState()['state'] && 'skip-step' != $fromPage) {
            return $this->redirectToRoute('debts', ['reportId' => $reportId]);
        }

        return [
            'comingFromLastStep' => 'skip-step' == $fromPage || 'last-step' == $fromPage,
            'report' => $report,
            'status' => $report->getStatus(),
        ];
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'debts';
    }
}
