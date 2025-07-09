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

/**
 * Base route.
 *
 * @Route("/report/{reportId}/pa-fee-expense")
 */
class PaFeeExpenseController extends AbstractController
{
    private static $jmsGroups = [
        'fee',
        'fee-state',
        'expenses', // second part uses same endpoints as deputy expenses
    ];

    public function __construct(
        private readonly RestClient $restClient,
        private readonly ReportApi $reportApi,
    ) {
    }

    /**
     * @Route("", name="pa_fee_expense")
     *
     * @Template("@App/Report/PaFeeExpense/start.html.twig")
     */
    public function startAction(int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (EntityDir\Report\Status::STATE_NOT_STARTED != $report->getStatus()->getPaFeesExpensesState()['state']) {
            return $this->redirectToRoute('pa_fee_expense_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/fee-exist", name="pa_fee_expense_fee_exist")
     *
     * @Template("@App/Report/PaFeeExpense/feeExist.html.twig")
     */
    public function feeExistAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Report\PaFeeExistType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($form['hasFees']->getData()) {
                case 'yes':
                    $report->setReasonForNoFees(null);
                    $this->restClient->put('report/'.$reportId, $report, ['reasonForNoFees']);

                    return $this->redirectToRoute('pa_fee_expense_fee_edit', ['reportId' => $reportId, 'from' => 'fee_exist']);
                case 'no':
                    $this->restClient->put('report/'.$reportId, $report, ['reasonForNoFees']);
                    // if 2nd seciont is complete, go to summary
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

    /**
     * @Route("/fee-edit", name="pa_fee_expense_fee_edit")
     *
     * @Template("@App/Report/PaFeeExpense/feeEdit.html.twig")
     */
    public function feeEditAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Report\FeesType::class, $report);
        $form->handleRequest($request);
        $fromPage = $request->get('from');

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->put('report/'.$report->getId(), $form->getData(), ['fee']);
            if ('summary' == $fromPage) {
                $request->getSession()->getFlashBag()->add('notice', 'Fee edited');

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

    /**
     * @Route("/other/exist", name="pa_fee_expense_other_exist")
     *
     * @Template("@App/Report/PaFeeExpense/otherExist.html.twig")
     */
    public function otherExistAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(
            FormDir\YesNoType::class,
            $report,
            ['field' => 'paidForAnything', 'translation_domain' => 'report-pa-fee-expense']
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Report */
            switch ($data->getPaidForAnything()) {
                case 'yes':
                    // the "yes" value is set automatically when expense are added. It cannot set by now or if the user leaves the page
                    // it'd leave the data inconsistent
                    return $this->redirectToRoute('pa_fee_expense_other_add', ['reportId' => $reportId]);
                case 'no':
                    $this->restClient->put('report/'.$reportId, $data, ['expenses-paid-anything']);

                    return $this->redirectToRoute('pa_fee_expense_summary', ['reportId' => $reportId]);
            }
        }

        $from = $request->get('from');
        $fromToRoute = [
            'summary' => 'pa_fee_expense_summary',
            'fee_exist' => 'pa_fee_expense_fee_exist',
        ];
        $backRoute = isset($fromToRoute[$from]) ? $fromToRoute[$from] : 'pa_fee_expense_fee_edit';
        $backLink = $this->generateUrl($backRoute, ['reportId' => $reportId]);

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/other/add", name="pa_fee_expense_other_add")
     *
     * @Template("@App/Report/PaFeeExpense/otherAdd.html.twig")
     */
    public function otherAddAction(Request $request, int $reportId): array|RedirectResponse
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

            $this->restClient->post('report/'.$report->getId().'/expense', $data, ['expenses']);

            return $this->redirect($this->generateUrl('pa_fee_expense_add_another', ['reportId' => $reportId]));
        }

        $from = $request->get('from');
        $fromToRoute = [
            'summary' => 'pa_fee_expense_summary',
            'add_another' => 'pa_fee_expense_add_another',
        ];
        $backRoute = isset($fromToRoute[$from]) ? $fromToRoute[$from] : 'pa_fee_expense_other_exist';
        $backLink = $this->generateUrl($backRoute, ['reportId' => $reportId]);

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/other/add-another", name="pa_fee_expense_add_another")
     *
     * @Template("@App/Report/PaFeeExpense/otherAddAnother.html.twig")
     */
    public function otherAddAnotherAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(FormDir\AddAnotherRecordType::class, $report, ['translation_domain' => 'report-pa-fee-expense']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('pa_fee_expense_other_add', ['reportId' => $reportId, 'from' => 'add_another']);
                case 'no':
                    return $this->redirectToRoute('pa_fee_expense_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/other-edit/{expenseId}", name="pa_fee_expense_edit")
     *
     * @Template("@App/Report/PaFeeExpense/otherEdit.html.twig")
     */
    public function otherEditAction(Request $request, int $reportId, int $expenseId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $expense = $this->restClient->get('report/'.$report->getId().'/expense/'.$expenseId, 'Report\Expense');

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

            $this->restClient->put('report/'.$report->getId().'/expense/'.$expense->getId(), $data, ['expenses']);

            return $this->redirect($this->generateUrl('pa_fee_expense', ['reportId' => $reportId]));
        }

        return [
            'backLink' => $this->generateUrl('pa_fee_expense_summary', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/other/delete/{expenseId}", name="pa_fee_expense_delete")
     *
     * @Template("@App/Common/confirmDelete.html.twig")
     */
    public function deleteAction(Request $request, int $reportId, int $expenseId): array|RedirectResponse
    {
        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($form->isSubmitted() && $form->isValid()) {
            $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

            $this->restClient->delete('report/'.$report->getId().'/expense/'.$expenseId);

            $request->getSession()->getFlashBag()->add(
                'notice',
                'Expense deleted'
            );

            return $this->redirect($this->generateUrl('pa_fee_expense', ['reportId' => $reportId]));
        }

        $expense = $this->restClient->get('report/'.$report->getId().'/expense/'.$expenseId, 'Report\Expense');

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

    /**
     * @Route("/summary", name="pa_fee_expense_summary")
     *
     * @Template("@App/Report/PaFeeExpense/summary.html.twig")
     */
    public function summaryAction(int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (EntityDir\Report\Status::STATE_NOT_STARTED == $report->getStatus()->getPaFeesExpensesState()['state']) {
            return $this->redirect($this->generateUrl('pa_fee_expense', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
        ];
    }
}
