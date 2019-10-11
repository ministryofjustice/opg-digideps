<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base route
 *
 * @Route("/report/{reportId}/pa-fee-expense")
 */
class PaFeeExpenseController extends AbstractController
{
    private static $jmsGroups = [
        'fee',
        'fee-state',
        'expenses', //second part uses same endpoints as deputy expenses
    ];

    /**
     * @Route("", name="pa_fee_expense")
     * @Template("AppBundle:Report/PaFeeExpense:start.html.twig")
     *
     * @param int $reportId
     *
     * @return array
     */
    public function startAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($report->getStatus()->getPaFeesExpensesState()['state'] != EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('pa_fee_expense_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/fee-exist", name="pa_fee_expense_fee_exist")
     * @Template("AppBundle:Report/PaFeeExpense:feeExist.html.twig")
     */
    public function feeExistAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Report\PaFeeExistType::class, $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['hasFees']->getData()) {
                case 'yes':
                    $report->setReasonForNoFees(null);
                    $this->getRestClient()->put('report/' . $reportId, $report, ['reasonForNoFees']);
                    return $this->redirectToRoute('pa_fee_expense_fee_edit', ['reportId' => $reportId, 'from'=>'fee_exist']);
                case 'no':
                    $this->getRestClient()->put('report/' . $reportId, $report, ['reasonForNoFees']);
                    // if 2nd seciont is complete, go to summary
                    $nextRoute = $report->isOtherFeesSectionComplete() ? 'pa_fee_expense_summary' : 'pa_fee_expense_other_exist';
                    return $this->redirectToRoute($nextRoute, ['reportId' => $reportId, 'from'=>'fee_exist']);
            }
        }

        $backRoute = $request->get('from') === 'summary' ? 'pa_fee_expense_summary' : 'pa_fee_expense';
        $backLink = $this->generateUrl($backRoute, ['reportId'=>$reportId]);

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/fee-edit", name="pa_fee_expense_fee_edit")
     * @Template("AppBundle:Report/PaFeeExpense:feeEdit.html.twig")
     */
    public function feeEditAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Report\FeesType::class, $report);
        $form->handleRequest($request);
        $fromPage = $request->get('from');

        if ($form->isValid()) {
            $this->getRestClient()->put('report/' . $report->getId(), $form->getData(), ['fee']);
            if ($fromPage == 'summary') {
                $request->getSession()->getFlashBag()->add('notice', 'Fee edited');
                return $this->redirectToRoute('pa_fee_expense_summary', ['reportId' => $reportId]);
            }

            $nextRoute = $report->isOtherFeesSectionComplete() ? 'pa_fee_expense_summary' : 'pa_fee_expense_other_exist';
            return $this->redirectToRoute($nextRoute, ['reportId' => $reportId]);
        }

        $backRoute = $request->get('from') === 'summary' ? 'pa_fee_expense_summary' : 'pa_fee_expense_fee_exist';
        $backLink = $this->generateUrl($backRoute, ['reportId'=>$reportId]);

        return [
            'backLink' => $backLink,
            'report' => $report,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/other/exist", name="pa_fee_expense_other_exist")
     * @Template("AppBundle:Report/PaFeeExpense:otherExist.html.twig")
     */
    public function otherExistAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\YesNoType::class, $report, [ 'field' => 'paidForAnything', 'translation_domain' => 'report-pa-fee-expense']
                                 );
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Report */
            switch ($data->getPaidForAnything()) {
                case 'yes':
                    // the "yes" value is set automatically when expense are added. It cannot set by now or if the user leaves the page
                    // it'd leave the data inconsistent
                    return $this->redirectToRoute('pa_fee_expense_other_add', ['reportId' => $reportId]);
                case 'no':
                    $this->getRestClient()->put('report/' . $reportId, $data, ['expenses-paid-anything']);
                    return $this->redirectToRoute('pa_fee_expense_summary', ['reportId' => $reportId]);
            }
        }

        $from = $request->get('from');
        $fromToRoute = [
            'summary' => 'pa_fee_expense_summary',
            'fee_exist' => 'pa_fee_expense_fee_exist',
        ];
        $backRoute = isset($fromToRoute[$from]) ? $fromToRoute[$from] : 'pa_fee_expense_fee_edit';
        $backLink = $this->generateUrl($backRoute, ['reportId'=>$reportId]);

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/other/add", name="pa_fee_expense_other_add")
     * @Template("AppBundle:Report/PaFeeExpense:otherAdd.html.twig")
     */
    public function otherAddAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
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

        if ($form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->getRestClient()->post('report/' . $report->getId() . '/expense', $data, ['expenses']);

            return $this->redirect($this->generateUrl('pa_fee_expense_add_another', ['reportId' => $reportId]));
        }

        $from = $request->get('from');
        $fromToRoute = [
            'summary' => 'pa_fee_expense_summary',
            'add_another' => 'pa_fee_expense_add_another',
        ];
        $backRoute = isset($fromToRoute[$from]) ? $fromToRoute[$from] : 'pa_fee_expense_other_exist';
        $backLink = $this->generateUrl($backRoute, ['reportId'=>$reportId]);

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/other/add-another", name="pa_fee_expense_add_another")
     * @Template("AppBundle:Report/PaFeeExpense:otherAddAnother.html.twig")
     */
    public function otherAddAnotherAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(FormDir\AddAnotherRecordType::class, $report, ['translation_domain' => 'report-pa-fee-expense']);
        $form->handleRequest($request);

        if ($form->isValid()) {
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
     * @Template("AppBundle:Report/PaFeeExpense:otherEdit.html.twig")
     *
     * @param Request $request
     * @param $reportId
     * @param $expenseId
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function otherEditAction(Request $request, $reportId, $expenseId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $expense = $this->getRestClient()->get('report/' . $report->getId() . '/expense/' . $expenseId, 'Report\Expense');

        $form = $this->createForm(
            FormDir\Report\DeputyExpenseType::class,
            $expense,
            [
                'user' => $this->getUser(),
                'report' => $report
            ]
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $request->getSession()->getFlashBag()->add('notice', 'Expense edited');

            $this->getRestClient()->put('report/' . $report->getId() . '/expense/' . $expense->getId(), $data, ['expenses']);

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
     * @Template("AppBundle:Common:confirmDelete.html.twig")
     *
     * @param Request $request
     * @param $reportId
     * @param $expenseId
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, $reportId, $expenseId)
    {
        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($form->isValid()) {
            $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

            $this->getRestClient()->delete('report/' . $report->getId() . '/expense/' . $expenseId);

            $request->getSession()->getFlashBag()->add(
                'notice',
                'Expense deleted'
            );

            return $this->redirect($this->generateUrl('pa_fee_expense', ['reportId' => $reportId]));
        }

        $expense = $this->getRestClient()->get('report/' . $report->getId() . '/expense/' . $expenseId, 'Report\Expense');

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
     * @Template("AppBundle:Report/PaFeeExpense:summary.html.twig")
     *
     * @param int $reportId
     *
     * @return array
     */
    public function summaryAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getPaFeesExpensesState()['state'] == EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirect($this->generateUrl('pa_fee_expense', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'paDeputyExpenses';
    }
}
