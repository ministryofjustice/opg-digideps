<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\Client\Internal\ReportApi;
use AppBundle\Service\Client\RestClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DeputyExpenseController extends AbstractController
{
    private static $jmsGroups = [
        'expenses',
        'expenses-state',
        'account'
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
     * @Route("/report/{reportId}/deputy-expenses", name="deputy_expenses")
     * @Template("AppBundle:Report/DeputyExpense:start.html.twig")
     *
     * @param int $reportId
     *
     * @return array|RedirectResponse
     */
    public function startAction(int $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($report->getStatus()->getExpensesState()['state'] != EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('deputy_expenses_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/deputy-expenses/exist", name="deputy_expenses_exist")
     * @Template("AppBundle:Report/DeputyExpense:exist.html.twig")
     *
     * @param Request $request
     * @param int $reportId
     *
     * @return array|RedirectResponse
     */
    public function existAction(Request $request, int $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\YesNoType::class, $report, [ 'field' => 'paidForAnything', 'translation_domain' => 'report-deputy-expenses']
                                 );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Report */
            switch ($data->getPaidForAnything()) {
                case 'yes':
                    return $this->redirectToRoute('deputy_expenses_add', ['reportId' => $reportId, 'from'=>'exist']);
                case 'no':
                    $this->restClient->put('report/' . $reportId, $data, ['expenses-paid-anything']);
                    return $this->redirectToRoute('deputy_expenses_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = $this->generateUrl('deputy_expenses', ['reportId' => $reportId]);
        if ($request->get('from') == 'summary') {
            $backLink = $this->generateUrl('deputy_expenses_summary', ['reportId' => $reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/deputy-expenses/add", name="deputy_expenses_add")
     * @Template("AppBundle:Report/DeputyExpense:add.html.twig")
     *
     * @param Request $request
     * @param int $reportId
     *
     * @return array|RedirectResponse
     */
    public function addAction(Request $request, int $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $expense = new EntityDir\Report\Expense();

        $form = $this->createForm(
            FormDir\Report\DeputyExpenseType::class,
            $expense,
            [
                'user' => $this->getUser(),
                'report' => $report
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->restClient->post('report/' . $report->getId() . '/expense', $data, ['expenses', 'account']);

            return $this->redirect($this->generateUrl('deputy_expenses_add_another', ['reportId' => $reportId]));
        }

        $backLinkRoute = 'deputy_expenses_' . $request->get('from');
        $backLink = $this->routeExists($backLinkRoute) ? $this->generateUrl($backLinkRoute, ['reportId'=>$reportId]) : '';

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/deputy-expenses/add_another", name="deputy_expenses_add_another")
     * @Template("AppBundle:Report/DeputyExpense:addAnother.html.twig")
     *
     * @param Request $request
     * @param int $reportId
     *
     * @return array|RedirectResponse
     */
    public function addAnotherAction(Request $request, int $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(FormDir\AddAnotherRecordType::class, $report, ['translation_domain' => 'report-deputy-expenses']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('deputy_expenses_add', ['reportId' => $reportId, 'from' => 'add_another']);
                case 'no':
                    return $this->redirectToRoute('deputy_expenses_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/deputy-expenses/edit/{expenseId}", name="deputy_expenses_edit")
     * @Template("AppBundle:Report/DeputyExpense:edit.html.twig")
     *
     * @param Request $request
     * @param int $reportId
     * @param int $expenseId
     *
     * @return array|RedirectResponse
     */
    public function editAction(Request $request, int $reportId, int $expenseId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $expense = $this->restClient->get(
            'report/' . $report->getId() . '/expense/' . $expenseId,
            'Report\Expense',
            [
                'expenses',
                'account'
            ]
        );

        if ($expense->getBankAccount() instanceof EntityDir\Report\BankAccount) {
            $expense->setBankAccountId($expense->getBankAccount()->getId());
        }

        $form = $this->createForm(
            FormDir\Report\DeputyExpenseType::class,
            $expense,
            [
                'user' => $this->getUser(),
                'report' => $report
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $request->getSession()->getFlashBag()->add('notice', 'Expense edited');

            $this->restClient->put(
                'report/' . $report->getId() . '/expense/' . $expense->getId(),
                $data,
                [
                    'expenses',
                    'account'
                ]
            );

            return $this->redirect($this->generateUrl('deputy_expenses', ['reportId' => $reportId]));
        }

        return [
            'backLink' => $this->generateUrl('deputy_expenses_summary', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/deputy-expenses/summary", name="deputy_expenses_summary")
     * @Template("AppBundle:Report/DeputyExpense:summary.html.twig")
     *
     * @param int $reportId
     *
     * @return array|RedirectResponse
     */
    public function summaryAction(int $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getExpensesState()['state'] == EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirect($this->generateUrl('deputy_expenses', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/deputy-expenses/{expenseId}/delete", name="deputy_expenses_delete")
     * @Template("AppBundle:Common:confirmDelete.html.twig")
     *
     * @param Request $request
     * @param int $reportId
     * @param int $expenseId
     * @return array|RedirectResponse
     */
    public function deleteAction(Request $request, int $reportId, int $expenseId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->delete('report/' . $report->getId() . '/expense/' . $expenseId);

            $request->getSession()->getFlashBag()->add(
                'notice',
                'Expense deleted'
            );

            return $this->redirect($this->generateUrl('deputy_expenses', ['reportId' => $reportId]));
        }

        $expense = $this->restClient->get('report/' . $reportId . '/expense/' . $expenseId, 'Report\Expense');

        return [
            'translationDomain' => 'report-deputy-expenses',
            'report' => $report,
            'form' => $form->createView(),
            'summary' => [
                ['label' => 'deletePage.summary.explanation', 'value' => $expense->getExplanation()],
                ['label' => 'deletePage.summary.amount', 'value' => $expense->getAmount(), 'format' => 'money'],
            ],
            'backLink' => $this->generateUrl('deputy_expenses', ['reportId' => $reportId]),
        ];
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'deputyExpenses';
    }
}
