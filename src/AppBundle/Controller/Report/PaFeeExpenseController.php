<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class PaFeeExpenseController extends AbstractController
{
    private static $jmsGroups = [
        'fee',
        'fee-state',
        'expenses', //second part uses same endpoints as deputy expenses
    ];

    /**
     * @Route("/report/{reportId}/pa-fee-expense", name="pa_fee_expense")
     * @Template()
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
     * @Route("/report/{reportId}/pa-fee-expense/fee-exist", name="pa_fee_expense_fee_exist")
     * @Template()
     */
    public function feeExistAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(new FormDir\Report\PaFeeExistType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['hasFees']->getData()) {
                case 'yes':
                    $report->setReasonForNoFees(null);
                    $this->getRestClient()->put('report/' . $reportId, $report, ['reasonForNoFees']);
                    return $this->redirectToRoute('pa_fee_expense_fee_edit', ['reportId' => $reportId, 'from'=>'exist']);
                case 'no':
                    $this->getRestClient()->put('report/' . $reportId, $report, ['reasonForNoFees']);
                    return $this->redirectToRoute('pa_fee_expense_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = $this->generateUrl('pa_fee_expense', ['reportId'=>$reportId]);
        if ($request->get('from') == 'summary') {
            $backLink = $this->generateUrl('pa_fee_expense_summary', ['reportId'=>$reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/pa-fee-expense/fee-edit", name="pa_fee_expense_fee_edit")
     * @Template()
     */
    public function feeEditAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(new FormDir\Report\FeesType(), $report);
        $form->handleRequest($request);
        $fromPage = $request->get('from');

        if ($form->isValid()) {
            $this->getRestClient()->put('report/' . $report->getId(), $form->getData(), ['fee']);
            if ($fromPage == 'summary') {
                $request->getSession()->getFlashBag()->add('notice', 'Fee edited');
                return $this->redirectToRoute('pa_fee_expense_summary', ['reportId' => $reportId]);
            }

            return $this->redirectToRoute('pa_fee_expense_other_exist', ['reportId' => $reportId]);
        }

        $backLink = $this->generateUrl('pa_fee_expense_fee_exist', ['reportId'=>$reportId]);
        if ($fromPage == 'summary') {
            $backLink = $this->generateUrl('pa_fee_expense_summary', ['reportId'=>$reportId]);
        }

        return [
            'backLink' => $backLink,
            'report' => $report,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/report/{reportId}/pa-fee-expense/other-exist", name="pa_fee_expense_other_exist")
     * @Template()
     */
    public function otherExistAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(new FormDir\YesNoType('paidForAnything', 'report-pa-fee-expense', ['yes' => 'Yes', 'no' => 'No']), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Report */
            switch ($data->getPaidForAnything()) {
                case 'yes':
                    return $this->redirectToRoute('IMPLEMENTMEpa_fee_expense_other_edit', ['reportId' => $reportId, 'from'=>'exist']);
                case 'no':
                    $this->getRestClient()->put('report/' . $reportId, $data, ['expenses-paid-anything']);
                    return $this->redirectToRoute('pa_fee_expense_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = $this->generateUrl('pa_fee_expense_fee_edit', ['reportId' => $reportId]);
        if ($request->get('from') == 'summary') {
            $backLink = $this->generateUrl('pa_fee_expense_summary', ['reportId' => $reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/pa-fee-expense/summary", name="pa_fee_expense_summary")
     * @Template()
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

}
