<?php

declare(strict_types=1);

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity\Report\BankAccount;
use App\Entity\Report\Expense;
use App\Entity\Report\Report;
use App\Entity\Report\Status;
use App\Form\AddAnotherRecordType;
use App\Form\ConfirmDeleteType;
use App\Form\Report\DeputyExpenseType;
use App\Form\YesNoType;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class DeputyExpenseController extends AbstractController
{
    private static array $jmsGroups = [
        'expenses',
        'expenses-state',
        'account',
    ];

    public function __construct(
        private readonly RestClient $restClient,
        private readonly ReportApi $reportApi,
    ) {
    }

    #[Route(path: '/report/{reportId}/deputy-expenses', name: 'deputy_expenses')]
    #[Template('@App/Report/DeputyExpense/start.html.twig')]
    public function startAction(int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (Status::STATE_NOT_STARTED != $report->getStatus()->getExpensesState()['state']) {
            return $this->redirectToRoute('deputy_expenses_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    #[Route(path: '/report/{reportId}/deputy-expenses/exist', name: 'deputy_expenses_exist')]
    #[Template('@App/Report/DeputyExpense/exist.html.twig')]
    public function existAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(
            YesNoType::class,
            $report,
            ['field' => 'paidForAnything', 'translation_domain' => 'report-deputy-expenses']
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* @var Report $data */
            $data = $form->getData();

            switch ($data->getPaidForAnything()) {
                case 'yes':
                    return $this->redirectToRoute('deputy_expenses_add', ['reportId' => $reportId, 'from' => 'exist']);
                case 'no':
                    $this->restClient->put('report/' . $reportId, $data, ['expenses-paid-anything']);

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

    #[Route(path: '/report/{reportId}/deputy-expenses/add', name: 'deputy_expenses_add')]
    #[Template('@App/Report/DeputyExpense/add.html.twig')]
    public function addAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $expense = new Expense();

        $form = $this->createForm(
            DeputyExpenseType::class,
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

            $this->restClient->post('report/' . $report->getId() . '/expense', $data, ['expenses', 'account']);

            return $this->redirect($this->generateUrl('deputy_expenses_add_another', ['reportId' => $reportId]));
        }

        try {
            $backLinkRoute = 'deputy_expenses_' . $request->get('from');
            $backLink = $this->generateUrl($backLinkRoute, ['reportId' => $reportId]);

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

    #[Route(path: '/report/{reportId}/deputy-expenses/add_another', name: 'deputy_expenses_add_another')]
    #[Template('@App/Report/DeputyExpense/addAnother.html.twig')]
    public function addAnotherAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(AddAnotherRecordType::class, $report, ['translation_domain' => 'report-deputy-expenses']);
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

    #[Route(path: '/report/{reportId}/deputy-expenses/edit/{expenseId}', name: 'deputy_expenses_edit')]
    #[Template('@App/Report/DeputyExpense/edit.html.twig')]
    public function editAction(Request $request, int $reportId, int $expenseId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $expense = $this->restClient->get(
            'report/' . $report->getId() . '/expense/' . $expenseId,
            'Report\Expense',
            [
                'expenses',
                'account',
            ]
        );

        if ($expense->getBankAccount() instanceof BankAccount) {
            $expense->setBankAccountId($expense->getBankAccount()->getId());
        }

        $form = $this->createForm(
            DeputyExpenseType::class,
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
                'report/' . $report->getId() . '/expense/' . $expense->getId(),
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

    #[Route(path: '/report/{reportId}/deputy-expenses/summary', name: 'deputy_expenses_summary')]
    #[Template('@App/Report/DeputyExpense/summary.html.twig')]
    public function summaryAction(int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (Status::STATE_NOT_STARTED == $report->getStatus()->getExpensesState()['state']) {
            return $this->redirect($this->generateUrl('deputy_expenses', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
        ];
    }

    #[Route(path: '/report/{reportId}/deputy-expenses/{expenseId}/delete', name: 'deputy_expenses_delete')]
    #[Template('@App/Common/confirmDelete.html.twig')]
    public function deleteAction(Request $request, int $reportId, int $expenseId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(ConfirmDeleteType::class);
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
}
