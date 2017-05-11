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
     * @Route("/report/{reportId}/pa-fee-expense/exist", name="pa_fee_expense_exist")
     * @Template()
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(new FormDir\Report\PaFeeExistType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['hasFees']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('pa_fee_expense_add', ['reportId' => $reportId, 'from'=>'exist']);
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
