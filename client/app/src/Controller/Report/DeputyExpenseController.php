<?php

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Form as FormDir;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class DeputyExpenseController extends AbstractController
{
    private static $jmsGroups = [
        'expenses',
        'expenses-state',
        'account',
    ];

    public function __construct(
        private readonly RestClient $restClient,
        private readonly ReportApi $reportApi,
    ) {
    }

    /**
     * @Route("/report/{reportId}/deputy-expenses", name="deputy_expenses")
     *
     * @Template("@App/Report/DeputyExpense/start.html.twig")
     */
    public function startAction(int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (EntityDir\Report\Status::STATE_NOT_STARTED != $report->getStatus()->getExpensesState()['state']) {
            return $this->redirectToRoute('deputy_expenses_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/deputy-expenses/exist", name="deputy_expenses_exist")
     *
     * @Template("@App/Report/DeputyExpense/exist.html.twig")
     */
    public function existAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(
            FormDir\YesNoType::class,
            $report,
            ['field' => 'paidForAnything', 'translation_domain' => 'report-deputy-expenses']
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Report */
            switch ($data->getPaidForAnything()) {
                case 'yes':
                    return $this->redirectToRoute('deputy_expenses_add', ['reportId' => $reportId, 'from' => 'exist']);
                case 'no':
                    $this->restClient->put('report/'.$reportId, $data, ['expenses-paid-anything']);

                    return $this->redirectToRoute('deputy_expenses_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = $this->generateUrl('deputy_expenses', ['reportId' => $reportId]);
        if ('summary' == $request->get('from')) {
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
     *
     * @Template("@App/Report/DeputyExpense/add.html.twig")
     */
    public function addAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $expense = new EntityDir\Report\Expense();

        $form = $this->createForm(
            FormDir\Report\DeputyExpenseType::class,
            $expense,
            [
                'user' => $this->getUser(),
                'report' => $report,
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->restClient->post('report/'.$report->getId().'/expense', $data, ['expenses', 'account']);

            return $this->redirect($this->generateUrl('deputy_expenses_add_another', ['reportId' => $reportId]));
        }

        try {
            $backLinkRoute = 'deputy_expenses_'.$request->get('from');
            $backLink = $this->generateUrl($backLinkRoute, ['reportId' => $reportId]);

            return [
                'backLink' => $backLink,
                'form' => $form->createView(),
                'report' => $report,
            ];
        } catch (RouteNotFoundException $e) {
            return [
                'backLink' => null,
                'form' => $form->createView(),
                'report' => $report,
            ];
        }
    }

    /**
     * @Route("/report/{reportId}/deputy-expenses/add_another", name="deputy_expenses_add_another")
     *
     * @Template("@App/Report/DeputyExpense/addAnother.html.twig")
     */
    public function addAnotherAction(Request $request, int $reportId): array|RedirectResponse
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
     *
     * @Template("@App/Report/DeputyExpense/edit.html.twig")
     */
    public function editAction(Request $request, int $reportId, int $expenseId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $expense = $this->restClient->get(
            'report/'.$report->getId().'/expense/'.$expenseId,
            'Report\Expense',
            [
                'expenses',
                'account',
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
                'report' => $report,
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $request->getSession()->getFlashBag()->add('notice', 'Expense edited');

            $this->restClient->put(
                'report/'.$report->getId().'/expense/'.$expense->getId(),
                $data,
                [
                    'expenses',
                    'account',
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
     *
     * @Template("@App/Report/DeputyExpense/summary.html.twig")
     */
    public function summaryAction(int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (EntityDir\Report\Status::STATE_NOT_STARTED == $report->getStatus()->getExpensesState()['state']) {
            return $this->redirect($this->generateUrl('deputy_expenses', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/deputy-expenses/{expenseId}/delete", name="deputy_expenses_delete")
     *
     * @Template("@App/Common/confirmDelete.html.twig")
     */
    public function deleteAction(Request $request, int $reportId, int $expenseId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->delete('report/'.$report->getId().'/expense/'.$expenseId);

            $request->getSession()->getFlashBag()->add(
                'notice',
                'Expense deleted'
            );

            return $this->redirect($this->generateUrl('deputy_expenses', ['reportId' => $reportId]));
        }

        $expense = $this->restClient->get('report/'.$reportId.'/expense/'.$expenseId, 'Report\Expense');

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
}
