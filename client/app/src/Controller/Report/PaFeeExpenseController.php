<?php

declare(strict_types=1);

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity\Report\Expense;
use App\Entity\Report\Report;
use App\Entity\Report\Status;
use App\Form\AddAnotherThingType;
use App\Form\ConfirmDeleteType;
use App\Form\Report\DeputyExpenseType;
use App\Form\Report\FeesType;
use App\Form\Report\PaFeeExistType;
use App\Form\YesNoType;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use OPG\Digideps\Common\Validating\ValidatingForm;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/report/{reportId}/pa-fee-expense')]
class PaFeeExpenseController extends AbstractController
{
    private static array $jmsGroups = [
        'fee',
        'fee-state',
        'expenses', //second part uses same endpoints as deputy expenses
    ];

    public function __construct(
        private readonly RestClient $restClient,
        private readonly ReportApi $reportApi,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '', name: 'pa_fee_expense')]
    #[Template('@App/Report/PaFeeExpense/start.html.twig')]
    public function startAction(int $reportId): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (Status::STATE_NOT_STARTED != $report->getStatus()->getPaFeesExpensesState()['state']) {
            return $this->redirectToRoute('pa_fee_expense_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    #[Route(path: '/fee-exist', name: 'pa_fee_expense_fee_exist')]
    #[Template('@App/Report/PaFeeExpense/feeExist.html.twig')]
    public function feeExistAction(Request $request, int $reportId): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(PaFeeExistType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $validatingForm = new ValidatingForm($form);
            $hasFees = $validatingForm->getStringOrNull('hasFees') === 'yes';

            if ($hasFees) {
                $report->setReasonForNoFees(null);
                $this->restClient->put('report/' . $reportId, $report, ['reasonForNoFees']);

                return $this->redirectToRoute('pa_fee_expense_fee_edit', ['reportId' => $reportId, 'from' => 'fee_exist']);
            } else {
                $this->restClient->put('report/' . $reportId, $report, ['reasonForNoFees']);
                // if 2nd section is complete, go to summary
                $nextRoute = $report->isOtherFeesSectionComplete() ? 'pa_fee_expense_summary' : 'pa_fee_expense_other_exist';

                return $this->redirectToRoute($nextRoute, ['reportId' => $reportId, 'from' => 'fee_exist']);
            }
        }

        $backRoute = 'summary' === $request->get('from') ? 'pa_fee_expense_summary' : 'pa_fee_expense';
        $backLink = $this->generateUrl($backRoute, ['reportId' => $reportId]);

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    #[Route(path: '/fee-edit', name: 'pa_fee_expense_fee_edit')]
    #[Template('@App/Report/PaFeeExpense/feeEdit.html.twig')]
    public function feeEditAction(Request $request, int $reportId): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FeesType::class, $report);

        $form->handleRequest($request);
        $fromPage = $request->get('from');

        if ($form->isSubmitted() && $form->isValid()) {
            $validatingForm = new ValidatingForm($form);
            $data = $validatingForm->getObjectOrThrow(null, Report::class);

            $this->restClient->put('report/' . $report->getId(), $data, ['fee']);
            if ('summary' == $fromPage) {
                $this->addFlash('notice', $this->translator->trans('notices.fee.edited', domain: 'report-pa-fee-expense'));

                return $this->redirectToRoute('pa_fee_expense_summary', ['reportId' => $reportId]);
            }

            $nextRoute = $report->isOtherFeesSectionComplete() ? 'pa_fee_expense_summary' : 'pa_fee_expense_other_exist';

            return $this->redirectToRoute($nextRoute, ['reportId' => $reportId]);
        }

        $backRoute = 'summary' === $request->get('from') ? 'pa_fee_expense_summary' : 'pa_fee_expense_fee_exist';
        $backLink = $this->generateUrl($backRoute, ['reportId' => $reportId]);

        return [
            'backLink' => $backLink,
            'report' => $report,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/other/exist', name: 'pa_fee_expense_other_exist')]
    #[Template('@App/Report/PaFeeExpense/otherExist.html.twig')]
    public function otherExistAction(Request $request, int $reportId): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(
            YesNoType::class,
            $report,
            ['field' => 'paidForAnything', 'translation_domain' => 'report-pa-fee-expense']
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $validatingForm = new ValidatingForm($form);
            $data = $validatingForm->getObjectOrThrow(null, Report::class);

            switch ($data->getPaidForAnything()) {
                case 'yes':
                    // the "yes" value is set automatically when expense are added. It cannot set by now or if the user leaves the page
                    // it'd leave the data inconsistent
                    return $this->redirectToRoute('pa_fee_expense_other_add', ['reportId' => $reportId]);
                case 'no':
                    $this->restClient->put('report/' . $reportId, $data, ['expenses-paid-anything']);

                    return $this->redirectToRoute('pa_fee_expense_summary', ['reportId' => $reportId]);
            }
        }

        $from = $request->get('from');
        $fromToRoute = [
            'summary' => 'pa_fee_expense_summary',
            'fee_exist' => 'pa_fee_expense_fee_exist',
        ];
        $backRoute = $fromToRoute[$from] ?? 'pa_fee_expense_fee_edit';
        $backLink = $this->generateUrl($backRoute, ['reportId' => $reportId]);

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    #[Route(path: '/other/add', name: 'pa_fee_expense_other_add')]
    #[Template('@App/Report/PaFeeExpense/otherAdd.html.twig')]
    public function otherAddAction(Request $request, int $reportId): RedirectResponse|array
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
        $form->add('addAnother', AddAnotherThingType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $validatingForm = new ValidatingForm($form);
            $data = $validatingForm->getObjectOrThrow(null, Expense::class);
            $data->setReport($report);

            $this->restClient->post('report/' . $report->getId() . '/expense', $data, ['expenses']);
            $this->addFlash('notice', $this->translator->trans('notices.entry.added', domain: 'report-pa-fee-expense'));
            $addAnother = $validatingForm->getStringOrNull('addAnother') === 'yes';
            return $this->redirect($this->generateUrl($addAnother ? 'pa_fee_expense_other_add' : 'pa_fee_expense_summary', ['reportId' => $reportId]));
        }

        return [
            'backLink' => $this->generateUrl('pa_fee_expense_summary', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    #[Route(path: '/other-edit/{expenseId}', name: 'pa_fee_expense_edit')]
    #[Template('@App/Report/PaFeeExpense/otherEdit.html.twig')]
    public function otherEditAction(Request $request, int $reportId, string $expenseId): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $expense = $this->restClient->get('report/' . $report->getId() . '/expense/' . $expenseId, 'Report\Expense');

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
            $validatingForm = new ValidatingForm($form);
            $data = $validatingForm->getObjectOrThrow(null, Expense::class);

            $this->restClient->put('report/' . $report->getId() . '/expense/' . $expense->getId(), $data, ['expenses']);
            $this->addFlash('notice', $this->translator->trans('notices.entry.edited', domain: 'report-pa-fee-expense'));

            return $this->redirect($this->generateUrl('pa_fee_expense', ['reportId' => $reportId]));
        }

        return [
            'backLink' => $this->generateUrl('pa_fee_expense_summary', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    #[Route(path: '/other/delete/{expenseId}', name: 'pa_fee_expense_delete')]
    #[Template('@App/Common/confirmDelete.html.twig')]
    public function deleteAction(Request $request, int $reportId, string $expenseId): RedirectResponse|array
    {
        $form = $this->createForm(ConfirmDeleteType::class);
        $form->handleRequest($request);

        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($form->isSubmitted() && $form->isValid()) {
            $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

            $this->restClient->delete('report/' . $report->getId() . '/expense/' . $expenseId);
            $this->addFlash('notice', $this->translator->trans('notices.entry.deleted', domain: 'report-pa-fee-expense'));

            return $this->redirect($this->generateUrl('pa_fee_expense', ['reportId' => $reportId]));
        }

        $expense = $this->restClient->get('report/' . $report->getId() . '/expense/' . $expenseId, 'Report\Expense');

        return [
            'translationDomain' => 'report-pa-fee-expense',
            'report' => $report,
            'form' => $form->createView(),
            'summary' => [
                ['label' => 'deletePage.summary.explanation', 'value' => $expense->getExplanation()],
                ['label' => 'deletePage.summary.amount', 'value' => $expense->getAmount(), 'format' => 'money'],
            ],
            'backLink' => $this->generateUrl('pa_fee_expense', ['reportId' => $reportId]),
        ];
    }

    #[Route(path: '/summary', name: 'pa_fee_expense_summary')]
    #[Template('@App/Report/PaFeeExpense/summary.html.twig')]
    public function summaryAction(int $reportId): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (Status::STATE_NOT_STARTED == $report->getStatus()->getPaFeesExpensesState()['state']) {
            return $this->redirect($this->generateUrl('pa_fee_expense', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
        ];
    }

    protected function getSectionId(): string
    {
        return 'paDeputyExpenses';
    }
}
